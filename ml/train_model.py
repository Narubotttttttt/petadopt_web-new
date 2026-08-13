# -*- coding: utf-8 -*-
"""
CAWS Pet Adoption System - Supervised Machine Learning Model Training
Algorithm: Random Forest Compatibility Classifier & Vector Distance Model
Framework: Scikit-Learn, Pandas, NumPy, Joblib
"""

import os
import sys
import json
import joblib
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import (
    accuracy_score, precision_score, recall_score,
    f1_score, classification_report, confusion_matrix
)

# Force UTF-8 output so Windows terminal does not crash on special characters
sys.stdout.reconfigure(encoding='utf-8', errors='replace')

TEMPERAMENT_TAGS = [
    'Friendly',
    'Calm',
    'Energetic',
    'Shy',
    'Playful',
    'Independent',
    'Affectionate',
    'Protective'
]


def generate_synthetic_dataset(num_samples=2500):
    """
    Generates a realistic shelter adopter-pet compatibility pairing
    dataset for supervised model training.
    Ground-truth labels are derived from expert shelter heuristics.
    """
    np.random.seed(42)

    living_environments  = ['apartment', 'house_with_yard', 'house_no_yard']
    activity_levels      = ['relaxed', 'moderate', 'high']
    experiences          = ['first_time', 'experienced']
    hours_alone_options  = ['0_3', '4_7', '8_plus']
    pet_species          = ['dog', 'cat']
    pet_age_cats         = ['kitten_puppy', 'young', 'adult', 'senior']
    colors               = ['Orange', 'Black', 'White', 'Brown', 'Tricolor', 'Gray', 'Tabby']

    data = []

    for _ in range(num_samples):
        # --- Adopter profile ---
        living      = np.random.choice(living_environments)
        activity    = np.random.choice(activity_levels)
        exp         = np.random.choice(experiences)
        has_kids    = int(np.random.rand() > 0.6)
        has_pets    = int(np.random.rand() > 0.5)
        hours       = np.random.choice(hours_alone_options)
        special_cap = int(np.random.rand() > 0.75)
        pref_col    = np.random.choice(colors + ['any'])

        # Desired temperament tags vector (8 binary features)
        desired_tags = np.random.randint(0, 2, size=len(TEMPERAMENT_TAGS))
        if np.sum(desired_tags) == 0:
            desired_tags[0] = 1  # Always at least 'Friendly'

        # --- Pet profile ---
        species         = np.random.choice(pet_species)
        age_cat         = np.random.choice(pet_age_cats)
        color           = np.random.choice(colors)
        pet_special     = int(np.random.rand() > 0.85)

        # Pet temperament tags vector (8 binary features)
        pet_tags = np.random.randint(0, 2, size=len(TEMPERAMENT_TAGS))
        if np.sum(pet_tags) == 0:
            pet_tags[np.random.randint(0, len(TEMPERAMENT_TAGS))] = 1

        # --- Ground-truth compatibility score (expert heuristics) ---
        score = 0.5

        # Temperament tag cosine similarity
        tag_dot   = np.dot(desired_tags, pet_tags)
        tag_mag   = np.sqrt(np.sum(desired_tags)) * np.sqrt(np.sum(pet_tags))
        tag_sim   = (tag_dot / tag_mag) if tag_mag > 0 else 0.5
        score    += tag_sim * 0.35

        # Living space
        if living == 'apartment':
            if species == 'cat' or pet_tags[1] == 1 or pet_tags[5] == 1:  # Calm / Independent
                score += 0.15
            elif species == 'dog' and pet_tags[2] == 1:                    # Energetic dog in apt
                score -= 0.15
        elif living == 'house_with_yard':
            if pet_tags[2] == 1 or pet_tags[4] == 1:                      # Energetic / Playful
                score += 0.15

        # Activity level
        if activity == 'high' and (pet_tags[2] == 1 or pet_tags[4] == 1):
            score += 0.15
        elif activity == 'relaxed' and pet_tags[1] == 1:
            score += 0.15

        # Ownership experience
        if exp == 'first_time':
            if pet_tags[0] == 1 or pet_tags[1] == 1:   # Friendly / Calm
                score += 0.10
            if pet_tags[7] == 1:                         # Protective
                score -= 0.10

        # Household dynamics
        if has_kids and (pet_tags[0] == 1 or pet_tags[4] == 1):  # Friendly / Playful
            score += 0.10
        if has_pets and (pet_tags[0] == 1 or pet_tags[4] == 1):
            score += 0.10

        # Hours alone
        if hours == '8_plus':
            if pet_tags[5] == 1 or pet_tags[1] == 1:   # Independent / Calm
                score += 0.15
            if age_cat == 'kitten_puppy':
                score -= 0.20
        elif hours == '0_3':
            if pet_tags[6] == 1 or age_cat == 'kitten_puppy':  # Affectionate
                score += 0.15

        # Special needs capacity
        if pet_special == 1:
            score += 0.20 if special_cap == 1 else -0.30

        # Color preference match
        if pref_col != 'any':
            score += 0.10 if pref_col.lower() == color.lower() else -0.05

        # Binary target: 1 = Compatible (High Match), 0 = Incompatible
        is_compatible = 1 if score >= 0.70 else 0

        # --- Build tabular feature row ---
        row = {
            'living_apartment' : int(living == 'apartment'),
            'living_yard'      : int(living == 'house_with_yard'),
            'living_no_yard'   : int(living == 'house_no_yard'),
            'activity_relaxed' : int(activity == 'relaxed'),
            'activity_moderate': int(activity == 'moderate'),
            'activity_high'    : int(activity == 'high'),
            'exp_first_time'   : int(exp == 'first_time'),
            'exp_experienced'  : int(exp == 'experienced'),
            'has_kids'         : has_kids,
            'has_other_pets'   : has_pets,
            'hours_0_3'        : int(hours == '0_3'),
            'hours_4_7'        : int(hours == '4_7'),
            'hours_8_plus'     : int(hours == '8_plus'),
            'special_care_cap' : special_cap,
            'is_dog'           : int(species == 'dog'),
            'is_cat'           : int(species == 'cat'),
            'age_kitten_puppy' : int(age_cat == 'kitten_puppy'),
            'age_young'        : int(age_cat == 'young'),
            'age_adult'        : int(age_cat == 'adult'),
            'age_senior'       : int(age_cat == 'senior'),
            'pet_special_need' : pet_special,
            'tag_similarity'   : round(float(tag_sim), 6),
            'color_matched'    : int(pref_col == 'any' or pref_col.lower() == color.lower()),
        }

        for i, tag in enumerate(TEMPERAMENT_TAGS):
            row[f'adopter_tag_{tag.lower()}'] = int(desired_tags[i])
        for i, tag in enumerate(TEMPERAMENT_TAGS):
            row[f'pet_tag_{tag.lower()}'] = int(pet_tags[i])

        row['is_compatible'] = is_compatible
        data.append(row)

    return pd.DataFrame(data)


def train_and_save_model():
    print("[1/4] Generating shelter training dataset (2,500 samples)...")
    df = generate_synthetic_dataset(num_samples=2500)

    feature_cols = [c for c in df.columns if c != 'is_compatible']
    X = df[feature_cols]
    y = df['is_compatible']

    print("[2/4] Splitting dataset: 80% Train / 20% Test ...")
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.20, random_state=42, stratify=y
    )

    print("[3/4] Training Random Forest Classifier (n_estimators=120, max_depth=12) ...")
    model = RandomForestClassifier(
        n_estimators=120,
        max_depth=12,
        min_samples_split=4,
        random_state=42,
        n_jobs=1
    )
    model.fit(X_train, y_train)

    # --- Evaluation ---
    y_pred = model.predict(X_test)
    acc    = accuracy_score(y_test, y_pred)
    prec   = precision_score(y_test, y_pred)
    rec    = recall_score(y_test, y_pred)
    f1     = f1_score(y_test, y_pred)
    cm     = confusion_matrix(y_test, y_pred).tolist()

    print("")
    print("========================================================")
    print("  MODEL TRAINING COMPLETE - EVALUATION METRICS")
    print("========================================================")
    print(f"  Accuracy  : {acc  * 100:.2f}%")
    print(f"  Precision : {prec * 100:.2f}%")
    print(f"  Recall    : {rec  * 100:.2f}%")
    print(f"  F1-Score  : {f1:.4f}")
    print("")
    print("  Classification Report:")
    print(classification_report(y_test, y_pred, target_names=['Incompatible', 'Compatible']))
    print("  Confusion Matrix:")
    print(np.array(cm))
    print("========================================================")
    print("")

    # --- Save artifacts ---
    script_dir    = os.path.dirname(os.path.abspath(__file__))
    model_path    = os.path.join(script_dir, 'pet_match_model.pkl')
    metrics_path  = os.path.join(script_dir, 'model_metrics.json')
    features_path = os.path.join(script_dir, 'feature_columns.json')

    joblib.dump(model, model_path)
    print(f"  Saved trained model    : {model_path}")

    with open(features_path, 'w') as f:
        json.dump(feature_cols, f, indent=2)
    print(f"  Saved feature schema   : {features_path}")

    metrics = {
        'model_name'       : 'Random Forest Compatibility Classifier',
        'framework'        : 'Scikit-Learn',
        'accuracy'         : round(acc  * 100, 2),
        'precision'        : round(prec * 100, 2),
        'recall'           : round(rec  * 100, 2),
        'f1_score'         : round(f1, 4),
        'confusion_matrix' : cm,
        'total_samples'    : len(df),
        'train_samples'    : len(X_train),
        'test_samples'     : len(X_test),
        'n_estimators'     : 120,
    }
    with open(metrics_path, 'w') as f:
        json.dump(metrics, f, indent=2)
    print(f"  Saved evaluation metrics: {metrics_path}")

    print("")
    print("[4/4] Model is ready for real-time inference in CAWS PetAdopt!")
    return model, feature_cols


if __name__ == '__main__':
    train_and_save_model()
