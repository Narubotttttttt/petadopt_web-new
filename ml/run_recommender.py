# -*- coding: utf-8 -*-
"""
CAWS ML Runner - Bootstrap wrapper for the pet recommendation engine.
Called by Laravel as: python ml/run_recommender.py
"""
import os
import sys
import io
import json
import importlib.util
import pathlib

# 1. Add user site-packages to path FIRST so joblib, sklearn, pandas are found.
_user_site = os.path.join(os.path.expanduser('~'),
                          'AppData', 'Roaming', 'Python', 'Python314', 'site-packages')
if _user_site not in sys.path:
    sys.path.insert(0, _user_site)

# 2. Read stdin RAW BYTES first before any TextIOWrapper reassignment.
#    PHP pipes the JSON payload through stdin. Reading the raw buffer here
#    avoids encoding mismatches from the subsequent TextIOWrapper wrapping.
_raw_stdin_bytes = sys.stdin.buffer.read()

# 3. Safe UTF-8 I/O for PHP subprocess output
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

# 4. Load pet_recommender.py as a module
_script = pathlib.Path(__file__).parent / 'pet_recommender.py'
spec = importlib.util.spec_from_file_location('pet_recommender', _script)
mod  = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)

# 5. Parse the stdin JSON payload
raw = _raw_stdin_bytes.decode('utf-8-sig', errors='replace').strip()
if not raw:
    sys.stdout.write(json.dumps({'error': 'No input received'}))
    sys.stdout.flush()
    sys.exit(1)

try:
    payload = json.loads(raw)
except json.JSONDecodeError as e:
    sys.stdout.write(json.dumps({'error': f'JSON parse error: {str(e)}'}))
    sys.stdout.flush()
    sys.exit(1)

# 6. Run recommendation and output JSON
recommendations = mod.recommend_pets(
    payload.get('pets', []),
    payload.get('adopter_profile', {})
)

output = {
    'success'        : True,
    'algorithm'      : 'Random Forest Classifier - Scikit-Learn (Trained Model)',
    'count'          : len(recommendations),
    'recommendations': recommendations,
}

sys.stdout.write(json.dumps(output, ensure_ascii=False))
sys.stdout.flush()
