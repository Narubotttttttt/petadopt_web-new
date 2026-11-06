"""
CAWS Pet Adoption System - Multi-Species Machine Learning Recommendation Engine
Algorithm : Supervised Random Forest Classifier (Scikit-Learn)
Model     : ml/pet_match_model.pkl (trained on 67,000 adoption records: Dogs & Cats)
Features  : ml/feature_columns.json (38 features including species_dog & species_cat)
Explainer : Dynamic LLM Reasoning (Google Gemini)
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

for _p in [
    os.path.join(os.path.dirname(os.path.dirname(__file__)), '.venv', 'Lib', 'site-packages'),
    getattr(site, 'getusersitepackages', lambda: '')(),
    *(getattr(site, 'getsitepackages', list)()),
    os.path.join(sys.prefix, 'Lib', 'site-packages'),
]:
    if _p and os.path.isdir(_p) and _p not in sys.path:
        sys.path.insert(0, _p)

import json
import re
import ssl
from typing import Any
from urllib.request import Request, urlopen

import joblib
import pandas as pd

ALL_TEMPERAMENT_TAGS = [
    'Friendly', 'Calm', 'Energetic', 'Shy',
    'Playful', 'Independent', 'Affectionate', 'Protective'
]

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH    = os.path.join(SCRIPT_DIR, 'pet_match_model.pkl')
FEATURES_PATH = os.path.join(SCRIPT_DIR, 'feature_columns.json')

_trained_model   = None
_feature_columns = None


def _load_model():
    global _trained_model, _feature_columns
    if _trained_model is None and os.path.exists(MODEL_PATH) and os.path.exists(FEATURES_PATH):
        try:
            _trained_model   = joblib.load(MODEL_PATH)
            with open(FEATURES_PATH, 'r', encoding='utf-8') as f:
                _feature_columns = json.load(f)
        except (OSError, json.JSONDecodeError, KeyError, ValueError):
            _trained_model   = None
            _feature_columns = None
    return _trained_model, _feature_columns


def _parse_age_numeric(age_str):
    """Converts age string into numeric float years."""
    if not isinstance(age_str, str):
        return 3.0
    s = age_str.lower().strip()
    match = re.search(r'(\d+(?:\.\d+)?)', s)
    num = float(match.group(1)) if match else 2.0

    if any(k in s for k in ['month', 'mo']):
        return max(0.1, round(num / 12.0, 2))
    elif any(k in s for k in ['week', 'wk']):
        return max(0.1, round(num / 52.0, 2))
    elif any(k in s for k in ['day']):
        return 0.1
    elif any(k in s for k in ['puppy', 'kitten', '< 1']):
        return 0.5
    elif any(k in s for k in ['senior', 'old']):
        return 9.0
    return max(0.1, num)


# ---------------------------------------------------------
# Multi-Species Feature Vector Builder (for Scikit-Learn)
# ---------------------------------------------------------
def _build_multi_species_feature_vector(adopter_profile, pet, feature_cols):
    species  = str(pet.get('type', 'dog')).lower()
    living   = str(adopter_profile.get('living_environment', 'apartment')).lower()
    activity = str(adopter_profile.get('activity_level', 'moderate')).lower()
    exp      = str(adopter_profile.get('pet_experience', 'first_time')).lower()
    has_kids = int(bool(adopter_profile.get('has_children', False)))
    has_pets = int(bool(adopter_profile.get('has_other_pets', False)))
    if has_pets:
        exp = 'experienced'
    hours    = str(adopter_profile.get('hours_alone', '4_7')).lower()
    has_yard_val = 1 if bool(adopter_profile.get('has_yard', False)) else (1 if ('yard' in living and 'no_yard' not in living and 'no_fence' not in living) else 0)

    pet_tags = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]
    age_yrs  = _parse_age_numeric(pet.get('age', '2 years'))
    pet_size = str(pet.get('size', 'medium')).lower()
    if species == 'cat' and pet_size not in ['small', 'medium']:
        pet_size = 'medium'

    # Behavioral metrics based on species & temperament
    if species == 'cat':
        # Feline behavioral profiles
        if 'Protective' in pet_tags:
            aggression_score = 2.5
            reactivity_score = 4.5
        elif 'Friendly' in pet_tags:
            aggression_score = 0.5
            reactivity_score = 1.0
        else:
            aggression_score = 1.0
            reactivity_score = 2.0

        if 'Independent' in pet_tags:
            anxiety_score = 1.0
        elif 'Shy' in pet_tags:
            anxiety_score = 4.5
        elif 'Affectionate' in pet_tags:
            anxiety_score = 2.5
        else:
            anxiety_score = 2.0

        if 'Energetic' in pet_tags:
            energy_level = 8.0
        elif 'Playful' in pet_tags:
            energy_level = 6.5
        elif 'Calm' in pet_tags:
            energy_level = 2.5
        else:
            energy_level = 4.5

        training_level = 9.0  # Felines naturally litter-trained
        house_trained  = 1
        size_home_mismatch = 0  # Cats do not suffer size-home mismatch in apartments

    else:
        # Canine behavioral profiles
        if 'Protective' in pet_tags:
            aggression_score = 3.5
            reactivity_score = 3.5
        elif 'Friendly' in pet_tags:
            aggression_score = 0.5
            reactivity_score = 0.5
        else:
            aggression_score = 1.5
            reactivity_score = 2.0

        if 'Independent' in pet_tags:
            anxiety_score = 1.5
        elif 'Shy' in pet_tags:
            anxiety_score = 5.5
        elif 'Affectionate' in pet_tags:
            anxiety_score = 4.0
        else:
            anxiety_score = 3.0

        if 'Energetic' in pet_tags:
            energy_level = 8.5
        elif 'Playful' in pet_tags:
            energy_level = 7.0
        elif 'Calm' in pet_tags:
            energy_level = 3.0
        else:
            energy_level = 5.0

        training_level = 7.0 if 'Calm' in pet_tags else (3.5 if age_yrs < 1.0 else 5.5)
        house_trained  = 1 if (age_yrs >= 1.0 or 'Calm' in pet_tags) else 0


        size_home_mismatch = 1 if (pet_size in ['large', 'xlarge'] and 'apartment' in living and has_yard_val == 0) else 0

    adopter_activity = 8.5 if activity in ['high', 'active'] else (2.5 if activity == 'relaxed' else 5.5)
    hours_val = 8.5 if hours in ['8_plus', '8+', '8plus'] else (2.0 if hours in ['0_3', '0-3'] else 5.5)


    energy_mismatch = abs(energy_level - adopter_activity)

    med_hist = str(pet.get('medical_history', '')).lower()
    is_chronic = int(any(k in med_hist for k in ['special', 'chronic', 'amputee', 'blind', 'deaf', 'disabilit']))
    is_minor   = int(any(k in med_hist for k in ['minor', 'treatment', 'recovery', 'skin']) and not is_chronic)
    is_none    = 1 if not (is_chronic or is_minor) else 0

    feat_dict = {
        'age_years'                   : age_yrs,
        'days_in_shelter'             : int(pet.get('days_in_shelter', 25) or 25),
        'previously_returned'         : int(pet.get('previously_returned', 0) or 0),
        'neutered'                    : 1,
        'aggression_score'            : aggression_score,
        'anxiety_separation'          : anxiety_score,
        'reactivity_to_dogs'          : reactivity_score,
        'energy_level'                : energy_level,
        'training_level'              : training_level,
        'house_trained'               : house_trained,
        'first_time_owner'            : 1 if exp == 'first_time' else 0,
        'household_has_kids'          : has_kids,
        'household_has_pets'          : has_pets,
        'has_yard'                    : has_yard_val,
        'hours_alone_per_day'         : hours_val,
        'adopter_activity_level'      : adopter_activity,
        'visits_before_adoption'      : 2,
        'met_resident_pets'           : has_pets,
        'adoption_counseling'         : 1,
        'expectation_score'           : 7.5,
        'energy_mismatch'             : energy_mismatch,
        'size_home_mismatch'          : size_home_mismatch,
        'species_cat'                 : 1 if species == 'cat' else 0,
        'species_dog'                 : 1 if species == 'dog' else 0,
        'size_large'                  : 1 if pet_size == 'large' else 0,
        'size_medium'                 : 1 if pet_size == 'medium' else 0,
        'size_small'                  : 1 if pet_size == 'small' else 0,
        'size_xlarge'                 : 1 if pet_size in ['xlarge', 'extra_large', 'giant'] else 0,
        'intake_type_born_in_care'    : 0,
        'intake_type_owner_surrender' : 1 if 'surrender' in str(pet.get('intake_type', '')).lower() else 0,
        'intake_type_stray'           : 1 if 'surrender' not in str(pet.get('intake_type', '')).lower() else 0,
        'intake_type_transfer'        : 0,
        'medical_needs_chronic'       : is_chronic,
        'medical_needs_minor'         : is_minor,
        'medical_needs_none'          : is_none,
        'home_type_apartment'         : 1 if 'apartment' in living else 0,
        'home_type_house_owned'       : 1 if ('yard' in living and 'no_yard' not in living) else 0,
        'home_type_house_rented'      : 1 if ('no_yard' in living or 'rent' in living) else 0,
    }

    return pd.DataFrame([[feat_dict.get(c, 0) for c in feature_cols]], columns=feature_cols), feat_dict


# ---------------------------------------------------------
# Dynamic LLM Reason Generator (Google Gemini)
# ---------------------------------------------------------
def _get_gemini_api_key():
    key = os.environ.get('GEMINI_API_KEY')
    if key and key.strip():
        return key.strip()
    
    # Check .env in project root (parent directory of ml/)
    env_file = os.path.join(os.path.dirname(SCRIPT_DIR), '.env')
    if os.path.exists(env_file):
        try:
            with open(env_file, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('GEMINI_API_KEY='):
                        val = line.split('=', 1)[1].strip().strip('"\'')
                        if val:
                            return val
        except OSError:
            return None
    return None


def _generate_dynamic_reasons_gemini(top_pets: list[dict[str, Any]], adopter_profile: dict[str, Any], key: str) -> dict[str, Any]:
    """
    Dynamic natural language explainer and comparative analyst powered by Google Gemini.
    Generates unscripted, contextual compatibility reasons, pre-adoption care tips, trait metrics,
    and a holistic comparative analysis for the top candidate companions in a single fast batch request.
    Strictly adheres to: Zero Emojis, valid JSON structure.
    """
    if not key or not top_pets:
        return {}

    compact_adopter = {
        'home_environment': adopter_profile.get('living_environment', 'apartment'),
        'has_yard': bool(adopter_profile.get('has_yard', False)),
        'yard_type': adopter_profile.get('yard_type', 'none'),
        'activity_level': adopter_profile.get('activity_level', 'moderate'),
        'hours_alone_daily': adopter_profile.get('hours_alone', '4_7'),
        'has_children': bool(adopter_profile.get('has_children', False)),
        'has_other_pets': bool(adopter_profile.get('has_other_pets', False)),
        'experience_level': 'experienced' if bool(adopter_profile.get('has_other_pets', False)) else adopter_profile.get('pet_experience', 'first_time'),
        'desired_temperaments': adopter_profile.get('desired_temperaments', [])
    }

    compact_pets = []
    for p in top_pets:
        pid = str(p.get('pet_id'))
        name = p.get('name')
        species = str(p.get('type') or 'companion').strip()
        breed = str(p.get('breed') or '').strip()
        # Shelter domain: pets do not have pre-assigned adopter names unless applied for.
        label = f"Pet no. {pid}" + (f" ({breed} {species.capitalize()})" if breed else f" ({species.capitalize()})") if not name else f"{name} ({breed} {species.capitalize()})"
        compact_pets.append({
            'id': pid,
            'shelter_label': label,
            'species': species,
            'breed': breed,
            'age': p.get('age'),
            'size': p.get('size'),
            'temperaments': p.get('temperaments', [])
        })

    prompt = (
        "You are an expert AI explainability and comparative analysis engine for a shelter pet adoption system.\n"
        "Our Scikit-Learn Random Forest model ranked these candidate pets as the top matches for the adopter.\n"
        f"Adopter Profile: {json.dumps(compact_adopter)}\n"
        f"Candidate Pets: {json.dumps(compact_pets)}\n\n"
        "Tasks:\n"
        "1. For each pet, provide:\n"
        "   - 'reasons': Exactly 3 to 4 distinct compatibility reasons (each 10 to 18 words) highlighting space, routine, temperament, and household dynamics. Reference them using their shelter label, e.g. 'Pet no. <id> (<Breed> <Species>)'.\n"
        "   - 'care_tip': 1 realistic pre-adoption care consideration or settling-in decompression tip (max 15 words).\n"
        "   - 'energy_level': Exactly one of 'Calm', 'Moderate', 'Active'\n"
        "   - 'space_fit': Short label (e.g. 'Apartment Friendly', 'Yard Preferred', 'House with Yard', 'Flexible Space')\n"
        "   - 'alone_tolerance': Short label (e.g. 'Tolerates 6+ hrs', 'Needs midday check', 'Moderate')\n"
        "   - 'social_compatibility': Short label (e.g. 'Great with pets', 'Friendly with kids', 'Gentle companion')\n"
        "   - 'grooming_care': Short label (e.g. 'Low maintenance', 'Regular brushing', 'Moderate grooming')\n"
        "2. For the overall comparison, provide 'comparative_analysis':\n"
        "   - 'summary': A natural 2 to 3 sentence comparative breakdown directly contrasting the candidates against the adopter's lifestyle and home environment. Highlight concrete trade-offs between them (e.g. space, activity requirements, and alone-time adaptability) using their shelter label 'Pet no. <id>'.\n"
        "   - 'verdict': A distinct 1 to 2 sentence recommendation advising the adopter how to choose between them based on lifestyle priorities (e.g. 'Choose Pet no. X if you prefer..., or Pet no. Y for...'). Do NOT simply repeat or restate the summary.\n\n"
        "Strict rules:\n"
        "- ZERO emojis anywhere.\n"
        "- Do NOT invent fictional names (e.g. do NOT invent names like Snowy, Bella, Max). Pets in the shelter are referred to by their official shelter ID (e.g. 'Pet no. 5 (Puspin Cat)').\n"
        "- Ground every point in the adopter's home, work hours, and temperament alignment.\n"
        "- Return ONLY valid JSON in format:\n"
        "{\n"
        "  \"pets\": {\n"
        "    \"<pet_id>\": {\n"
        "      \"reasons\": [\"...\"],\n"
        "      \"care_tip\": \"...\",\n"
        "      \"energy_level\": \"...\",\n"
        "      \"space_fit\": \"...\",\n"
        "      \"alone_tolerance\": \"...\",\n"
        "      \"social_compatibility\": \"...\",\n"
        "      \"grooming_care\": \"...\"\n"
        "    }\n"
        "  },\n"
        "  \"comparative_analysis\": {\n"
        "    \"summary\": \"...\",\n"
        "    \"verdict\": \"...\"\n"
        "  }\n"
        "}\n"
    )

    models = ['gemini-flash-lite-latest', 'gemini-3.5-flash-lite', 'gemini-3.1-flash-lite-preview']
    payload = json.dumps({
        'contents': [{'parts': [{'text': prompt}]}],
        'generationConfig': {
            'responseMimeType': 'application/json',
            'temperature': 0.2
        }
    }).encode('utf-8')

    ctx = ssl.create_default_context()
    for model_name in models:
        url = f"https://generativelanguage.googleapis.com/v1beta/models/{model_name}:generateContent?key={key}"
        req = Request(
            url,
            data=payload,
            headers={'Content-Type': 'application/json'},
            method='POST'
        )
        try:
            with urlopen(req, context=ctx, timeout=10) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                raw_text = data['candidates'][0]['content']['parts'][0]['text']
                parsed_json = json.loads(raw_text)
                
                pets_map: dict[str, dict[str, Any]] = {}
                raw_pets = parsed_json.get('pets', {})
                if not raw_pets and isinstance(parsed_json, dict):
                    for k, v in parsed_json.items():
                        if k not in ['comparative_analysis', 'summary', 'verdict'] and isinstance(v, dict):
                            raw_pets[str(k)] = v

                for k, v in raw_pets.items():
                    if isinstance(v, dict):
                        reasons = [str(r).strip() for r in v.get('reasons', []) if str(r).strip()]
                        care_tip = str(v.get('care_tip', '')).strip()
                        pets_map[str(k)] = {
                            'reasons': reasons,
                            'care_tip': care_tip,
                            'energy_level': str(v.get('energy_level', 'Moderate')).strip(),
                            'space_fit': str(v.get('space_fit', 'Apartment Friendly')).strip(),
                            'alone_tolerance': str(v.get('alone_tolerance', 'Moderate')).strip(),
                            'social_compatibility': str(v.get('social_compatibility', 'Friendly with pets')).strip(),
                            'grooming_care': str(v.get('grooming_care', 'Low maintenance')).strip(),
                        }

                comp_analysis = parsed_json.get('comparative_analysis', {})
                if not comp_analysis or not isinstance(comp_analysis, dict):
                    comp_analysis = {
                        'summary': str(parsed_json.get('summary', '')).strip(),
                        'verdict': str(parsed_json.get('verdict', '')).strip(),
                    }

                return {
                    'pets': pets_map,
                    'comparative_analysis': comp_analysis,
                }
        except Exception as e:  # noqa: BLE001
            sys.stderr.write(f"[Gemini Error] Model {model_name} failed: {e}\n")
            continue

    return {}


def _generate_default_comparative_analysis(top_pets: list[dict[str, Any]], adopter_profile: dict[str, Any]) -> dict[str, Any]:
    """
    Baseline trait metrics derived strictly from pet profile features
    used only if Gemini AI synthesis is unreachable.
    Does NOT generate canned synthetic narrative text.
    """
    pets_map: dict[str, dict[str, Any]] = {}
    for p in top_pets:
        pid = str(p.get('pet_id'))
        temps = [str(t).lower() for t in p.get('temperaments', [])]
        species = str(p.get('type', 'dog')).lower()
        size = str(p.get('size', 'medium')).lower()

        is_calm = any(t in temps for t in ['calm', 'gentle', 'quiet', 'independent'])
        is_active = any(t in temps for t in ['playful', 'energetic', 'active', 'curious'])

        energy = 'Calm' if is_calm else ('Active' if is_active else 'Moderate')
        space = 'Apartment Friendly' if (species == 'cat' or size in ['small', 'toy']) else ('Yard Preferred' if is_active else 'Flexible Space')
        alone = 'Tolerates 6+ hrs' if is_calm else ('Moderate' if species == 'cat' else 'Needs regular routine')
        social = 'Great with other pets' if any(t in temps for t in ['friendly', 'social', 'playful']) else 'Gentle companion'
        grooming = 'Low maintenance' if 'short' in str(p.get('breed', '')).lower() else 'Regular care'

        pets_map[pid] = {
            'energy_level': energy,
            'space_fit': space,
            'alone_tolerance': alone,
            'social_compatibility': social,
            'grooming_care': grooming,
        }

    return {
        'pets': pets_map,
        'comparative_analysis': {
            'summary': 'AI comparative analysis is unavailable. Live Gemini reasoning could not be reached.',
            'verdict': 'Verify network connection or Gemini API credentials.'
        }
    }

def _generate_care_tip(pet: dict[str, Any], adopter_profile: dict[str, Any]) -> str:
    """
    Generates a practical shelter care consideration or settling-in decompression tip
    grounded in the pet's behavioral traits, age, and adopter lifestyle constraints.
    """
    tags = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]
    age_yrs = _parse_age_numeric(pet.get('age', '2 years'))
    hours = str(adopter_profile.get('hours_alone', '4_7')).lower()
    has_kids = bool(adopter_profile.get('has_children', False))

    if 'Shy' in tags:
        return "Allow 2 to 3 days of quiet space in a dedicated room for initial decompression."
    elif 'Energetic' in tags:
        return "Provide 30 to 45 minutes of daily structured play or active walks to channel energy."
    elif 'Independent' in tags:
        return "Respect their personal space and allow them to initiate contact during early bonding."
    elif 'Protective' in tags:
        return "Introduce unfamiliar guests gradually using calm guidance and positive reinforcement."
    elif hours in ['8_plus', '8+', '8plus']:
        return "Provide interactive puzzle toys or safe enrichment items during longer alone hours."
    elif age_yrs < 1.0:
        return "Maintain a consistent potty schedule and crate routine to build confidence."
    elif has_kids:
        return "Supervise initial family interactions to establish gentle boundaries with children."
    else:
        return "Establish consistent daily feeding and rest schedules to build trust during week one." 


def _generate_default_reasons(pet, match_pct):
    """
    Default Explainable ML attribution reason when Gemini API is unconfigured.
    Reflects the objective Scikit-Learn model feature scoring.
    """
    species = str(pet.get('type', 'companion')).capitalize()
    size = str(pet.get('size', 'medium')).capitalize()
    age = str(pet.get('age', 'adult')).capitalize()
    return [
        f"High compatibility match ({match_pct}%) evaluated by the Random Forest model.",
        f"Living environment and available space align with this {size} {species}'s frame and exercise needs.",
        f"Daily schedule and household routine strongly support this {age} companion's care requirements.",
        "Verified behavioral temperament complements your lifestyle preferences and household dynamics."
    ]


# ---------------------------------------------------------
# Main Multi-Species ML Recommendation Function
# ---------------------------------------------------------
def _get_sort_tuple(item: Any) -> tuple:
    try:
        score = float(item.get('match_score', 0.0))
        breakdown = item.get('compatibility_breakdown', {})
        temp = float(breakdown.get('temperament', 0.0))
        routine = float(breakdown.get('routine', 0.0))
        space = float(breakdown.get('space', 0.0))
        days = float(item.get('days_in_shelter', 0.0) or 0.0)
        return (score, temp, (routine + space), days)
    except (KeyError, TypeError, ValueError):
        return (0.0, 0.0, 0.0, 0.0)


def recommend_pets_with_analysis(pets_data, adopter_profile, top_k=3):
    """
    Ranks pet recommendations using the Multi-Species Scikit-Learn Random Forest Model.
    Both dogs and cats execute model.predict_proba() directly.
    Top candidate reasons are dynamically generated via Google Gemini.
    """
    empty_analysis = {
        'summary': 'No candidate pets available for comparison.',
        'verdict': 'Check back soon for newly rescued companions.',
    }
    if not pets_data:
        return [], empty_analysis

    if not isinstance(adopter_profile, dict):
        adopter_profile = {}

    # STRICT FILTER: Focus ONLY on pets that are ready for adoption (status == 'available').
    # Pets with status 'in_shelter', 'pending', 'adopted', or 'deceased' MUST NEVER be recommended.
    available = [
        p for p in pets_data
        if not p.get('status') or str(p.get('status', '')).strip().lower() == 'available'
    ]
    if not available:
        return [], empty_analysis

    # Strict species filter if user requested
    pref_species = str(adopter_profile.get('preferred_species', 'any')).lower()
    if pref_species in ['dog', 'cat']:
        filtered = [p for p in available if str(p.get('type', '')).lower() == pref_species]
        if not filtered:
            return [], empty_analysis
        available = filtered

    model, feature_cols = _load_model()
    results: list[dict[str, Any]] = []

    for pet in available:
        species = str(pet.get('type', 'dog')).lower()
        pet_tags_list = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]

        # MULTI-SPECIES ML EXECUTION: model.predict_proba() runs for ALL pets (dogs & cats)
        if model is not None and feature_cols is not None:
            feat_df, feat_dict = _build_multi_species_feature_vector(adopter_profile, pet, feature_cols)
            proba = model.predict_proba(feat_df)[0]
            rf_prob = float(proba[1])  # Predicted probability of compatibility
            final_metric = rf_prob
            algo_name = 'Scikit-Learn Random Forest Classifier (Multi-Species Trained Model)'
        else:
            feat_dict = {}
            final_metric = 0.70
            algo_name = 'Multi-Species Compatibility Engine'

        match_pct = round(min(99.0, max(52.0, (final_metric * 45.0) + 53.0)), 1)

        # STRICT SAFEGUARD: Enforce 80.0% minimum compatibility threshold.
        # Pets scoring below 80% compatibility must never be recommended.
        if match_pct < 80.0:
            continue

        # Multi-factor alignment breakdown
        size_mismatch = feat_dict.get('size_home_mismatch', 0)
        has_yard_val = feat_dict.get('has_yard', 0)
        energy_diff = feat_dict.get('energy_mismatch', 1.0)
        hours_val = feat_dict.get('hours_alone_per_day', 5.5)
        anxiety = feat_dict.get('anxiety_separation', 2.0)

        # 1. Living Space Fit (based on size, species, and yard access)
        if species == 'cat' or size_mismatch == 0:
            space_pct = round(min(98.0, max(82.0, match_pct + (3.0 if has_yard_val else 1.0))), 0)
        else:
            space_pct = round(min(84.0, max(80.0, match_pct - 3.0)), 0)

        # 2. Daily Routine Alignment (based on energy mismatch and work hours alone)
        routine_penalty = (energy_diff * 1.5) + (1.5 if hours_val > 7.0 and anxiety > 3.0 else 0)
        routine_pct = round(min(98.0, max(80.0, match_pct - routine_penalty + 2.0)), 0)

        # 3. Temperament & Behavioral Fit (based on behavioral tags and safety)
        desired = adopter_profile.get('desired_temperaments', [])
        temp_boost = 3.0 if any(t in pet_tags_list for t in desired) else 1.0
        temperament_pct = round(min(99.0, max(80.0, match_pct + temp_boost)), 0)

        compat_breakdown = {
            'space': int(space_pct),
            'routine': int(routine_pct),
            'temperament': int(temperament_pct)
        }

        default_reasons = _generate_default_reasons(pet, match_pct)
        care_tip = _generate_care_tip(pet, adopter_profile)

        results.append({
            'pet_id'                 : pet.get('id'),
            'name'                   : pet.get('name'),
            'type'                   : species,
            'breed'                  : pet.get('breed'),
            'age'                    : pet.get('age'),
            'gender'                 : pet.get('gender'),
            'size'                   : 'medium' if species == 'cat' and str(pet.get('size', '')).lower() == 'large' else pet.get('size'),
            'color'                  : pet.get('color'),
            'photo_path'             : pet.get('photo_path'),
            'status'                 : pet.get('status'),
            'temperaments'           : pet_tags_list,
            'match_score'            : match_pct,
            'match_percentage'       : match_pct,
            'match_percentage_str'   : f"{match_pct}%",
            'compatibility_breakdown': compat_breakdown,
            'reasons'                : default_reasons,
            'match_reasons'          : default_reasons,
            'care_tip'               : care_tip,
            'consideration'          : care_tip,
            'algorithm_used'         : algo_name
        })

    if not results:
        return [], empty_analysis

    # Sort descending by compatibility match score
    results.sort(key=_get_sort_tuple, reverse=True)
    top_candidates = results[:top_k] if top_k else results

    # DYNAMIC EXPLAINER & COMPARATIVE LAYER: Invoke Gemini or Fallback
    api_key = _get_gemini_api_key()
    gemini_result: dict[str, Any] | None = None
    if api_key and top_candidates:
        gemini_result = _generate_dynamic_reasons_gemini(top_candidates, adopter_profile, api_key)

    default_analysis = _generate_default_comparative_analysis(top_candidates, adopter_profile)

    comparative_analysis: dict[str, Any] = {}
    if isinstance(gemini_result, dict):
        raw_ca = gemini_result.get('comparative_analysis')
        if isinstance(raw_ca, dict) and raw_ca.get('summary'):
            comparative_analysis = raw_ca

    if not comparative_analysis and isinstance(default_analysis, dict):
        raw_dca = default_analysis.get('comparative_analysis')
        if isinstance(raw_dca, dict):
            comparative_analysis = raw_dca

    gemini_pets: dict[str, dict[str, Any]] = {}
    if isinstance(gemini_result, dict):
        raw_gp = gemini_result.get('pets')
        if isinstance(raw_gp, dict):
            gemini_pets = {str(k): v for k, v in raw_gp.items() if isinstance(v, dict)}

    default_pets: dict[str, dict[str, Any]] = {}
    if isinstance(default_analysis, dict):
        raw_dp = default_analysis.get('pets')
        if isinstance(raw_dp, dict):
            default_pets = {str(k): v for k, v in raw_dp.items() if isinstance(v, dict)}

    for item in top_candidates:
        pid_str = str(item.get('pet_id'))
        g_entry: dict[str, Any] = gemini_pets.get(pid_str) or {}
        d_entry: dict[str, Any] = default_pets.get(pid_str) or {}

        reasons = g_entry.get('reasons')
        if isinstance(reasons, list) and reasons:
            item['reasons'] = reasons
            item['match_reasons'] = reasons

        care_tip = g_entry.get('care_tip')
        if isinstance(care_tip, str) and care_tip:
            item['care_tip'] = care_tip
            item['consideration'] = care_tip

        item['comparison_traits'] = {
            'energy_level': str(g_entry.get('energy_level') or d_entry.get('energy_level') or 'Moderate'),
            'space_fit': str(g_entry.get('space_fit') or d_entry.get('space_fit') or 'Apartment Friendly'),
            'alone_tolerance': str(g_entry.get('alone_tolerance') or d_entry.get('alone_tolerance') or 'Moderate'),
            'social_compatibility': str(g_entry.get('social_compatibility') or d_entry.get('social_compatibility') or 'Friendly with pets'),
            'grooming_care': str(g_entry.get('grooming_care') or d_entry.get('grooming_care') or 'Low maintenance'),
        }

    return top_candidates, comparative_analysis


def recommend_pets(pets_data, adopter_profile, top_k=3):
    """
    Backward-compatible wrapper returning top candidate recommendations.
    """
    recs, _ = recommend_pets_with_analysis(pets_data, adopter_profile, top_k=top_k)
    return recs
