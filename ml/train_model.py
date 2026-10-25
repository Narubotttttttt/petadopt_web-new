# -*- coding: utf-8 -*-
"""
CAWS Pet Adoption System - Supervised Machine Learning Model Training
Dataset  : Multi-Species Pet Adoption Master Dataset (67,000 Records: Dogs & Cats)
Algorithm: Random Forest Multi-Species Compatibility Classifier (Scikit-Learn)
Target   : is_compatible (1 = Adoption Kept & Thriving, 0 = Returned Mismatch)
"""

import os
import sys

# Ensure Windows system environment variables are present for Winsock and C-extensions
if sys.platform == 'win32':
    if 'SystemRoot' not in os.environ:
        os.environ['SystemRoot'] = os.environ.get('SYSTEMROOT', r'C:\Windows')
    if 'windir' not in os.environ:
        os.environ['windir'] = os.environ.get('WINDIR', r'C:\Windows')

import site
import json

# Ensure virtualenv and Python site-packages are loaded
for _p in [
    os.path.join(os.path.dirname(os.path.dirname(__file__)), '.venv', 'Lib', 'site-packages'),
    getattr(site, 'getusersitepackages', lambda: '')(),
    *(getattr(site, 'getsitepackages', lambda: [])()),
    os.path.join(sys.prefix, 'Lib', 'site-packages'),
]:
    if _p and os.path.isdir(_p) and _p not in sys.path:
        sys.path.insert(0, _p)

import joblib
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import (
    accuracy_score, precision_score, recall_score,
    f1_score, roc_auc_score, classification_report, confusion_matrix
)

# Force UTF-8 output so Windows terminal does not crash on special characters
if hasattr(sys.stdout, 'reconfigure'):
    getattr(sys.stdout, 'reconfigure')(encoding='utf-8', errors='replace')


def train_and_save_model():
    script_dir    = os.path.dirname(os.path.abspath(__file__))
    data_csv_path = os.path.join(script_dir, 'pet_adoption_master.csv')
    model_path    = os.path.join(script_dir, 'pet_match_model.pkl')
    metrics_path  = os.path.join(script_dir, 'model_metrics.json')
    features_path = os.path.join(script_dir, 'feature_columns.json')

    print("==================================================================")
    print("  CAWS PET ADOPTION SYSTEM - MULTI-SPECIES MODEL TRAINING         ")
    print("==================================================================")

    if not os.path.exists(data_csv_path):
        raise FileNotFoundError(f"Missing master dataset at: {data_csv_path}")

    print(f"[1/4] Loading multi-species adoption records from: {data_csv_path}")
    df = pd.read_csv(data_csv_path)
    print(f"  [+] Total records loaded: {len(df):,} (Dogs: {(df['species']=='dog').sum():,}, Cats: {(df['species']=='cat').sum():,})")

    # 1. Target Column: returned (0 = Kept/Success, 1 = Returned/Mismatch)
    # Convert to is_compatible: 1 = Compatible, 0 = Incompatible
    y = (1 - df['returned']).astype(int)

    # 2. Exclude identifiers, weight_kg (per requirement), and post-adoption leakage columns
    exclude_cols = ['adoption_id', 'weight_kg', 'return_reason', 'days_to_return', 'returned']
    raw_feature_cols = [c for c in df.columns if c not in exclude_cols]

    # 3. Categorical one-hot encoding (includes species: species_cat, species_dog)
    categorical_cols = ['species', 'size', 'intake_type', 'medical_needs', 'home_type']
    X = pd.get_dummies(df[raw_feature_cols], columns=categorical_cols, dtype=int)
    feature_cols = X.columns.tolist()

    print(f"[2/4] Prepared {len(feature_cols)} clean feature columns.")
    print(f"  [+] Target distribution: {y.value_counts().to_dict()} (1=Compatible, 0=Incompatible)")

    # 4. Train / Test Split (Stratified 80/20)
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.20, random_state=42, stratify=y
    )

    print(f"[3/4] Training Multi-Species Random Forest Classifier on {len(X_train):,} training samples...")
    model = RandomForestClassifier(
        n_estimators=120,
        max_depth=12,
        min_samples_split=5,
        class_weight='balanced',
        random_state=42,
        n_jobs=-1
    )
    model.fit(X_train, y_train)

    # 5. Model Evaluation
    y_pred = model.predict(X_test)
    y_prob = model.predict_proba(X_test)[:, 1]

    acc    = float(accuracy_score(y_test, y_pred))
    prec   = float(precision_score(y_test, y_pred, zero_division=0))
    rec    = float(recall_score(y_test, y_pred, zero_division=0))
    f1     = float(f1_score(y_test, y_pred, zero_division=0))
    roc    = float(roc_auc_score(y_test, y_prob))
    cm     = confusion_matrix(y_test, y_pred).tolist()

    print("")
    print("==================================================================")
    print("  MODEL TRAINING COMPLETE - MULTI-SPECIES EVALUATION METRICS      ")
    print("==================================================================")
    print(f"  Accuracy  : {acc * 100:.2f}%")
    print(f"  Precision : {prec * 100:.2f}%")
    print(f"  Recall    : {rec * 100:.2f}%")
    print(f"  F1-Score  : {f1:.4f}")
    print(f"  ROC-AUC   : {roc:.4f}")
    print("")
    print("  Classification Report:")
    print(classification_report(y_test, y_pred, target_names=['Incompatible', 'Compatible']))
    print("  Confusion Matrix:")
    print(np.array(cm))
    print("==================================================================")

    # Top feature importances
    importances = pd.Series(model.feature_importances_, index=feature_cols).sort_values(ascending=False)
    print("\n  Top 10 Most Predictive Features:")
    for feat_name, imp_val in importances.head(10).items():
        print(f"    - {feat_name:25}: {imp_val * 100:.2f}%")
    print("")

    # 6. Save Artifacts
    joblib.dump(model, model_path, compress=3)
    print(f"  [+] Saved trained model (compressed): {model_path}")

    with open(features_path, 'w') as f:
        json.dump(feature_cols, f, indent=2)
    print(f"  [+] Saved feature schema            : {features_path}")

    metrics = {
        'model_name'       : 'Random Forest Multi-Species Compatibility Classifier (Canine & Feline)',
        'framework'        : 'Scikit-Learn',
        'dataset_source'   : 'Multi-Species Pet Adoption Master Dataset (Kaggle & ASPCA)',
        'total_samples'    : len(df),
        'train_samples'    : len(X_train),
        'test_samples'     : len(X_test),
        'features_count'   : len(feature_cols),
        'accuracy'         : round(acc * 100, 2),
        'precision'        : round(prec * 100, 2),
        'recall'           : round(rec * 100, 2),
        'f1_score'         : round(f1, 4),
        'roc_auc'          : round(roc, 4),
        'confusion_matrix' : cm,
        'top_features'     : {k: round(float(v), 4) for k, v in importances.head(10).items()},
        'n_estimators'     : 120,
        'max_depth'        : 12
    }
    with open(metrics_path, 'w') as f:
        json.dump(metrics, f, indent=2)
    print(f"  [+] Saved evaluation metrics        : {metrics_path}")

    print("")
    print("[4/4] Multi-species ML model is ready for real-time inference in CAWS PetAdopt!")
    return model, feature_cols


if __name__ == '__main__':
    train_and_save_model()
