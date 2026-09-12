# -*- coding: utf-8 -*-
"""
CAWS Pet Adoption System - Supervised Machine Learning Model Training
Dataset  : Austin Animal Center (AAC) Real Shelter Adoption Dataset (78,000+ records)
Algorithm: Random Forest Compatibility Classifier & Vector Space Distance Model
Framework: Scikit-Learn, Pandas, NumPy, Joblib

Weighting Distribution:
  - Coat Color Match            : 22% (Primary Demographics)
  - Age Group Match             : 20% (Primary Demographics)
  - Gender Match                : 20% (Primary Demographics)
  - Temperament Cosine Sim      : 19% (Behavioral Alignment)
  - Adopter Capability/Lifestyle: 19% (Living, Space, Routine, Special Care)
  Total Demographics = 62% > Temperament & Capability = 38%
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

COLOR_FAMILIES = {
    'orange': ['orange', 'ginger', 'red', 'yellow', 'gold'],
    'black': ['black', 'dark', 'charcoal'],
    'white': ['white', 'cream', 'ivory', 'light'],
    'brown': ['brown', 'tan', 'chocolate', 'fawn', 'buff', 'cocoa'],
    'tricolor': ['tricolor', 'calico', 'tortie', 'torbie', 'multi'],
    'gray': ['gray', 'grey', 'silver', 'blue', 'smoke'],
    'tabby': ['tabby', 'striped', 'brindle', 'tiger'],
}


def _parse_age_category(age_str):
    """Parses real shelter age string into standard categories."""
    if not isinstance(age_str, str):
        return 'adult'
    s = age_str.lower().strip()
    if any(k in s for k in ['day', 'week', 'month', '0 year', '0 years', 'puppy', 'kitten', '< 1']):
        return 'kitten_puppy'
    elif any(k in s for k in ['1 year', '2 year', 'young', 'junior']):
        return 'young'
    elif any(k in s for k in ['3 year', '4 year', '5 year', '6 year', '7 year', 'adult']):
        return 'adult'
    elif any(k in s for k in ['8 year', '9 year', '10 year', '11 year', '12 year', '13 year', '14 year', '15 year', '16 year', '17 year', '18 year', '19 year', '20 year', 'senior', 'old']):
        return 'senior'
    return 'adult'


def _parse_color(color_str):
    """Maps raw shelter color descriptions to standard color categories."""
    if not isinstance(color_str, str):
        return 'Brown'
    c = color_str.lower()
    if any(k in c for k in ['orange', 'ginger', 'red', 'gold', 'yellow']):
        return 'Orange'
    elif 'black' in c:
        return 'Black'
    elif 'white' in c or 'cream' in c or 'ivory' in c:
        return 'White'
    elif any(k in c for k in ['calico', 'tricolor', 'torbie', 'tortie']):
        return 'Tricolor'
    elif any(k in c for k in ['blue', 'gray', 'grey', 'silver']):
        return 'Gray'
    elif any(k in c for k in ['tabby', 'tiger', 'brindle', 'striped']):
        return 'Tabby'
    elif any(k in c for k in ['brown', 'tan', 'chocolate', 'fawn', 'buff']):
        return 'Brown'
    return 'Brown'


def _parse_gender(sex_str):
    """Parses gender from shelter sex description (e.g., 'Neutered Male', 'Spayed Female')."""
    if not isinstance(sex_str, str):
        return 'Unknown'
    s = sex_str.lower()
    if 'female' in s or 'spayed' in s:
        return 'Female'
    elif 'male' in s or 'neutered' in s:
        return 'Male'
    return 'Unknown'


def _match_color(pref_col, pet_color):
    """Calculates color match score (0.0 to 1.0). Default 'any' = 1.0."""
    pref = str(pref_col or 'any').lower().strip()
    if pref in ['any', 'all', '', 'any color']:
        return 1.0
    
    pet_col = str(pet_color or '').lower().strip()
    if not pet_col:
        return 0.5
    
    # Exact or substring match
    if pref in pet_col or pet_col in pref:
        return 1.0
    
    # Family match
    for family, aliases in COLOR_FAMILIES.items():
        pref_in_family = any(a in pref for a in aliases) or family in pref
        pet_in_family = any(a in pet_col for a in aliases) or family in pet_col
        if pref_in_family and pet_in_family:
            return 1.0
    
    # Partial multi-color match
    if '/' in pet_col or '-' in pet_col:
        parts = pet_col.replace('-', '/').split('/')
        for part in parts:
            part = part.strip()
            if part in pref or pref in part:
                return 0.75
            for family, aliases in COLOR_FAMILIES.items():
                if any(a in pref for a in aliases) and any(a in part for a in aliases):
                    return 0.75

    return 0.0


def _match_age(pref_age, pet_age_cat):
    """Calculates age match score (0.0 to 1.0). Default 'any' = 1.0."""
    pref = str(pref_age or 'any').lower().strip()
    if pref in ['any', 'all', '', 'any age']:
        return 1.0
    
    # Normalize preference
    if any(k in pref for k in ['puppy', 'kitten', '< 1', 'kitten_puppy']):
        p_cat = 'kitten_puppy'
    elif any(k in pref for k in ['young', '1 - 3', 'junior']):
        p_cat = 'young'
    elif any(k in pref for k in ['senior', '8+', 'old']):
        p_cat = 'senior'
    else:
        p_cat = 'adult'
        
    if p_cat == pet_age_cat:
        return 1.0
    
    # Close age range
    adjacency = {
        'kitten_puppy': ['young'],
        'young': ['kitten_puppy', 'adult'],
        'adult': ['young', 'senior'],
        'senior': ['adult'],
    }
    if pet_age_cat in adjacency.get(p_cat, []):
        return 0.4
    return 0.0


def _match_gender(pref_gender, pet_gender):
    """Calculates gender match score (0.0 to 1.0). Default 'any' = 1.0."""
    pref = str(pref_gender or 'any').lower().strip()
    if pref in ['any', 'all', '', 'any gender']:
        return 1.0
    
    pet_g = str(pet_gender or '').lower().strip()
    if not pet_g or pet_g == 'unknown':
        return 0.5
    
    if pref == 'male' and ('male' in pet_g and 'female' not in pet_g):
        return 1.0
    if pref == 'female' and 'female' in pet_g:
        return 1.0
    return 0.0


def _infer_pet_temperaments(species, age_cat, breed, name):
    """
    Infers realistic behavioral temperament tags based on animal type,
    age development stage, and breed traits from shelter records.
    """
    breed_l = str(breed).lower()
    tags = set()

    if species == 'dog':
        tags.add('Friendly')
        if age_cat == 'kitten_puppy':
            tags.update(['Playful', 'Energetic'])
        elif age_cat == 'young':
            tags.update(['Energetic', 'Playful'])
        elif age_cat == 'senior':
            tags.update(['Calm', 'Affectionate', 'Independent'])
        else:
            tags.add('Affectionate')

        # Breed specific traits
        if any(b in breed_l for b in ['shepherd', 'terrier', 'husky', 'collie', 'boxer', 'bull', 'hound', 'pointer']):
            tags.add('Energetic')
            if 'shepherd' in breed_l or 'guard' in breed_l:
                tags.add('Protective')
        if any(b in breed_l for b in ['retriever', 'labrador', 'spaniel', 'beagle', 'poodle', 'golden']):
            tags.update(['Friendly', 'Playful', 'Affectionate'])
        if any(b in breed_l for b in ['chihuahua', 'shih tzu', 'pug', 'bichon', 'maltese', 'dachs']):
            tags.update(['Affectionate', 'Calm'])

    else: # Cat
        if age_cat == 'kitten_puppy':
            tags.update(['Playful', 'Energetic', 'Affectionate'])
        elif age_cat == 'senior':
            tags.update(['Calm', 'Independent'])
        else:
            tags.update(['Calm', 'Independent', 'Affectionate'])
            if 'siamese' in breed_l:
                tags.add('Playful')
            if 'persian' in breed_l or 'ragdoll' in breed_l:
                tags.add('Calm')

    # Guarantee at least 1 and at most 3 tags
    tags_list = [t for t in TEMPERAMENT_TAGS if t in tags]
    if not tags_list:
        tags_list = ['Friendly', 'Calm'] if species == 'dog' else ['Calm', 'Independent']

    vec = np.zeros(len(TEMPERAMENT_TAGS), dtype=int)
    for t in tags_list:
        vec[TEMPERAMENT_TAGS.index(t)] = 1
    return vec, tags_list


def build_real_shelter_dataset(raw_csv_path, num_samples=3500):
    """
    Loads real Austin Animal Center shelter intake/outcome records,
    cleans attributes, pairs with adopter personas, and creates
    the complete enhanced feature compatibility dataset.
    """
    np.random.seed(42)
    print(f"  [+] Loading real shelter records from: {raw_csv_path}")

    df_raw = pd.read_csv(raw_csv_path)
    df_shelter = df_raw[df_raw['animal_type'].isin(['Dog', 'Cat'])].copy()

    # Prioritize successful adoptions first, then transfers
    if 'outcome_type' in df_shelter.columns:
        df_adopted = df_shelter[df_shelter['outcome_type'] == 'Adoption']
        if len(df_adopted) >= num_samples:
            df_sample = df_adopted.sample(n=num_samples, random_state=42).reset_index(drop=True)
        else:
            df_sample = df_shelter.sample(n=min(num_samples, len(df_shelter)), random_state=42).reset_index(drop=True)
    else:
        df_sample = df_shelter.sample(n=min(num_samples, len(df_shelter)), random_state=42).reset_index(drop=True)

    living_environments  = ['apartment', 'house_with_yard', 'house_no_yard']
    activity_levels      = ['relaxed', 'moderate', 'high']
    experiences          = ['first_time', 'experienced']
    hours_alone_options  = ['0_3', '4_7', '8_plus']
    all_colors           = ['Orange', 'Black', 'White', 'Brown', 'Tricolor', 'Gray', 'Tabby']
    all_ages             = ['kitten_puppy', 'young', 'adult', 'senior']
    all_genders          = ['male', 'female']

    dataset = []

    for _, pet_row in df_sample.iterrows():
        # --- 1. Real Shelter Pet Attributes ---
        species     = str(pet_row.get('animal_type', 'Dog')).lower()
        pet_name    = str(pet_row.get('name', 'Pet'))
        raw_age     = str(pet_row.get('age_upon_outcome', '1 year'))
        age_cat     = _parse_age_category(raw_age)
        raw_color   = str(pet_row.get('color', 'Brown'))
        color       = _parse_color(raw_color)
        raw_sex     = str(pet_row.get('sex_upon_outcome', 'Neutered Male'))
        pet_gender  = _parse_gender(raw_sex)
        breed       = str(pet_row.get('breed', 'Mixed Breed'))
        outcome_sub = str(pet_row.get('outcome_subtype', '')).lower()
        pet_special = int(any(k in outcome_sub for k in ['special', 'medical', 'suffering', 'injured', 'behavior']))

        pet_tags_vec, pet_tags_list = _infer_pet_temperaments(species, age_cat, breed, pet_name)

        # --- 2. Adopter Persona Generation ---
        living      = np.random.choice(living_environments)
        activity    = np.random.choice(activity_levels)
        exp         = np.random.choice(experiences)
        has_kids    = int(np.random.rand() > 0.6)
        has_pets    = int(np.random.rand() > 0.5)
        hours       = np.random.choice(hours_alone_options)
        special_cap = int(np.random.rand() > 0.8)

        # Adopter Demographic Preferences
        pref_col    = np.random.choice(all_colors + ['any'], p=[0.11, 0.11, 0.11, 0.11, 0.11, 0.11, 0.11, 0.23])
        pref_age    = np.random.choice(all_ages + ['any'], p=[0.20, 0.20, 0.20, 0.15, 0.25])
        pref_gender = np.random.choice(all_genders + ['any'], p=[0.35, 0.35, 0.30])

        # Desired temperament tags vector
        num_desired = np.random.randint(1, 4)
        desired_idx = np.random.choice(len(TEMPERAMENT_TAGS), size=num_desired, replace=False)
        desired_tags = np.zeros(len(TEMPERAMENT_TAGS), dtype=int)
        desired_tags[desired_idx] = 1
        desired_tags_list = [TEMPERAMENT_TAGS[i] for i in desired_idx]

        # --- 3. Enhanced Compatibility Calculation with Color (22%), Age (20%), Gender (20%) Priority ---
        # 3.1 Primary Demographics Match (62% total weight)
        color_match_score  = _match_color(pref_col, color)
        age_match_score    = _match_age(pref_age, age_cat)
        gender_match_score = _match_gender(pref_gender, pet_gender)

        demo_score = (0.22 * color_match_score) + (0.20 * age_match_score) + (0.20 * gender_match_score)

        # 3.2 Temperament Cosine Similarity (19% weight)
        tag_dot = np.dot(desired_tags, pet_tags_vec)
        tag_mag = np.sqrt(np.sum(desired_tags)) * np.sqrt(np.sum(pet_tags_vec))
        tag_sim = float(tag_dot / tag_mag) if tag_mag > 0 else 0.50
        temperament_score = 0.19 * tag_sim

        # 3.3 Adopter Capability & Lifestyle Compatibility (19% weight)
        lifestyle_points = 0.60  # baseline suitability

        # Living space compatibility
        if living == 'apartment':
            if species == 'cat' or pet_tags_vec[1] == 1 or pet_tags_vec[5] == 1: # Calm / Independent
                lifestyle_points += 0.20
            elif species == 'dog' and pet_tags_vec[2] == 1:                    # High energy dog in apt
                lifestyle_points -= 0.20
        elif living == 'house_with_yard':
            if pet_tags_vec[2] == 1 or pet_tags_vec[4] == 1:                  # Energetic / Playful
                lifestyle_points += 0.20

        # Activity level alignment
        if activity == 'high' and (pet_tags_vec[2] == 1 or pet_tags_vec[4] == 1):
            lifestyle_points += 0.15
        elif activity == 'relaxed' and pet_tags_vec[1] == 1:
            lifestyle_points += 0.15

        # Experience level
        if exp == 'first_time':
            if pet_tags_vec[0] == 1 or pet_tags_vec[1] == 1:  # Friendly / Calm
                lifestyle_points += 0.10
            if pet_tags_vec[7] == 1:                            # Protective
                lifestyle_points -= 0.15

        # Children & other pets safety
        if has_kids and (pet_tags_vec[0] == 1 or pet_tags_vec[4] == 1): # Friendly / Playful
            lifestyle_points += 0.10
        if has_pets and (pet_tags_vec[0] == 1 or pet_tags_vec[4] == 1):
            lifestyle_points += 0.10

        # Hours alone
        if hours == '8_plus':
            if pet_tags_vec[5] == 1 or pet_tags_vec[1] == 1:  # Independent / Calm
                lifestyle_points += 0.15
            if age_cat == 'kitten_puppy':
                lifestyle_points -= 0.25
        elif hours == '0_3':
            if pet_tags_vec[6] == 1 or age_cat == 'kitten_puppy': # Affectionate
                lifestyle_points += 0.15

        # Special care capacity
        if pet_special == 1:
            lifestyle_points += 0.25 if special_cap == 1 else -0.35

        lifestyle_normalized = min(1.0, max(0.0, lifestyle_points))
        capability_score = 0.19 * lifestyle_normalized

        # Final Total Composite Score (0.0 to 1.0)
        total_compatibility = demo_score + temperament_score + capability_score
        is_compatible = 1 if total_compatibility >= 0.65 else 0

        # --- 4. Tabular Feature Vector ---
        row = {
            # Human-readable columns for Excel inspection
            'pet_name'                 : pet_name if pet_name != 'nan' else 'Shelter Rescue',
            'pet_species'              : species,
            'pet_breed'                : breed,
            'pet_age_group'            : age_cat,
            'pet_color'                : color,
            'pet_gender'               : pet_gender,
            'pet_temperaments'         : '; '.join(pet_tags_list),
            'adopter_living'           : living,
            'adopter_activity'         : activity,
            'adopter_experience'       : exp,
            'adopter_has_kids'         : has_kids,
            'adopter_has_pets'         : has_pets,
            'adopter_hours_alone'      : hours,
            'adopter_pref_color'       : pref_col,
            'adopter_pref_age'         : pref_age,
            'adopter_pref_gender'      : pref_gender,
            'adopter_desired_tags'     : '; '.join(desired_tags_list),

            # Core Demographic Match Features (Primary Priority: 62%)
            'color_matched'            : round(float(color_match_score), 4),
            'age_matched'              : round(float(age_match_score), 4),
            'gender_matched'           : round(float(gender_match_score), 4),
            'is_dog'                   : int(species == 'dog'),
            'is_cat'                   : int(species == 'cat'),
            'pet_age_kitten_puppy'     : int(age_cat == 'kitten_puppy'),
            'pet_age_young'            : int(age_cat == 'young'),
            'pet_age_adult'            : int(age_cat == 'adult'),
            'pet_age_senior'           : int(age_cat == 'senior'),
            'pet_is_male'              : int(pet_gender.lower() == 'male'),
            'pet_is_female'            : int(pet_gender.lower() == 'female'),
            'pref_age_kitten_puppy'    : int(pref_age == 'kitten_puppy'),
            'pref_age_young'           : int(pref_age == 'young'),
            'pref_age_adult'           : int(pref_age == 'adult'),
            'pref_age_senior'          : int(pref_age == 'senior'),
            'pref_gender_male'         : int(pref_gender == 'male'),
            'pref_gender_female'       : int(pref_gender == 'female'),

            # Behavioral / Temperament Features (19%)
            'tag_similarity'           : round(float(tag_sim), 6),

            # Adopter Capability & Lifestyle Features (19%)
            'living_apartment'         : int(living == 'apartment'),
            'living_yard'              : int(living == 'house_with_yard'),
            'living_no_yard'           : int(living == 'house_no_yard'),
            'activity_relaxed'         : int(activity == 'relaxed'),
            'activity_moderate'        : int(activity == 'moderate'),
            'activity_high'            : int(activity == 'high'),
            'exp_first_time'           : int(exp == 'first_time'),
            'exp_experienced'          : int(exp == 'experienced'),
            'has_kids'                 : has_kids,
            'has_other_pets'           : has_pets,
            'hours_0_3'                : int(hours == '0_3'),
            'hours_4_7'                : int(hours == '4_7'),
            'hours_8_plus'             : int(hours == '8_plus'),
            'special_care_cap'         : special_cap,
            'pet_special_need'         : pet_special,
        }

        for i, tag in enumerate(TEMPERAMENT_TAGS):
            row[f'adopter_tag_{tag.lower()}'] = int(desired_tags[i])
        for i, tag in enumerate(TEMPERAMENT_TAGS):
            row[f'pet_tag_{tag.lower()}'] = int(pet_tags_vec[i])

        row['is_compatible'] = is_compatible
        dataset.append(row)

    df_dataset = pd.DataFrame(dataset)
    return df_dataset


def train_and_save_model():
    script_dir    = os.path.dirname(os.path.abspath(__file__))
    raw_csv_path  = os.path.join(script_dir, 'aac_shelter_outcomes.csv')
    dataset_csv   = os.path.join(script_dir, 'shelter_adoption_dataset.csv')
    model_path    = os.path.join(script_dir, 'pet_match_model.pkl')
    metrics_path  = os.path.join(script_dir, 'model_metrics.json')
    features_path = os.path.join(script_dir, 'feature_columns.json')

    print("==================================================================")
    print("  CAWS PET ADOPTION SYSTEM - TRAINING ON REAL SHELTER DATASET     ")
    print("==================================================================")

    # 1. Build dataset from Austin Animal Center CSV
    if os.path.exists(raw_csv_path):
        print(f"[1/4] Processing real shelter animals from: {raw_csv_path}")
        df = build_real_shelter_dataset(raw_csv_path, num_samples=3500)
    else:
        print(f"[1/4] {raw_csv_path} not found, generating benchmark dataset...")
        raise FileNotFoundError(f"Missing dataset at {raw_csv_path}")

    # Save clean dataset for Excel inspection and documentation
    df.to_csv(dataset_csv, index=False)
    print(f"  [+] Saved full dataset for Excel inspection : {dataset_csv}")

    # Extract ML feature columns (excluding text metadata and target column)
    meta_cols = [
        'pet_name', 'pet_species', 'pet_breed', 'pet_age_group', 'pet_color', 'pet_gender',
        'pet_temperaments', 'adopter_living', 'adopter_activity',
        'adopter_experience', 'adopter_has_kids', 'adopter_has_pets',
        'adopter_hours_alone', 'adopter_pref_color', 'adopter_pref_age',
        'adopter_pref_gender', 'adopter_desired_tags', 'is_compatible'
    ]
    feature_cols = [c for c in df.columns if c not in meta_cols]

    X = df[feature_cols]
    y = df['is_compatible']

    print(f"[2/4] Splitting dataset: 80% Train ({int(len(df)*0.8)} samples) / 20% Test ({int(len(df)*0.2)} samples)...")
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.20, random_state=42, stratify=y
    )

    print(f"[3/4] Training Random Forest Classifier on {len(feature_cols)} features (n_estimators=160, max_depth=15)...")
    model = RandomForestClassifier(
        n_estimators=160,
        max_depth=15,
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
    print("==================================================================")
    print("  MODEL TRAINING COMPLETE - EVALUATION METRICS (AUSTIN SHELTER)  ")
    print("==================================================================")
    print(f"  Accuracy  : {acc  * 100:.2f}%")
    print(f"  Precision : {prec * 100:.2f}%")
    print(f"  Recall    : {rec  * 100:.2f}%")
    print(f"  F1-Score  : {f1:.4f}")
    print("")
    print("  Classification Report:")
    print(classification_report(y_test, y_pred, target_names=['Incompatible', 'Compatible']))
    print("  Confusion Matrix:")
    print(np.array(cm))
    print("==================================================================")
    print("")

    # --- Save artifacts ---
    joblib.dump(model, model_path)
    print(f"  [+] Saved trained model    : {model_path}")

    with open(features_path, 'w') as f:
        json.dump(feature_cols, f, indent=2)
    print(f"  [+] Saved feature schema   : {features_path}")

    metrics = {
        'model_name'       : 'Random Forest Compatibility Classifier (Color, Age, Gender Prioritized)',
        'framework'        : 'Scikit-Learn',
        'dataset_source'   : 'Austin Animal Center Real Shelter Outcomes (Kaggle)',
        'accuracy'         : round(acc  * 100, 2),
        'precision'        : round(prec * 100, 2),
        'recall'           : round(rec  * 100, 2),
        'f1_score'         : round(f1, 4),
        'confusion_matrix' : cm,
        'total_samples'    : len(df),
        'train_samples'    : len(X_train),
        'test_samples'     : len(X_test),
        'n_estimators'     : 160,
        'features_count'   : len(feature_cols),
        'weights'          : {
            'color': '22%',
            'age': '20%',
            'gender': '20%',
            'temperament': '19%',
            'capability_lifestyle': '19%'
        }
    }
    with open(metrics_path, 'w') as f:
        json.dump(metrics, f, indent=2)
    print(f"  [+] Saved evaluation metrics: {metrics_path}")

    print("")
    print("[4/4] Model is ready for real-time inference in CAWS PetAdopt!")
    return model, feature_cols


if __name__ == '__main__':
    train_and_save_model()
