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

# 1. Add user site-packages and standard site-packages to path so joblib, sklearn, pandas are found.
import site
for _p in [
    getattr(site, 'getusersitepackages', lambda: '')(),
    *(getattr(site, 'getsitepackages', lambda: [])()),
    os.path.join(sys.prefix, 'Lib', 'site-packages'),
]:
    if _p and os.path.isdir(_p) and _p not in sys.path:
        sys.path.insert(0, _p)

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
if spec is None or spec.loader is None:
    sys.stdout.write(json.dumps({'error': 'Failed to load pet_recommender module'}))
    sys.stdout.flush()
    sys.exit(1)

mod = importlib.util.module_from_spec(spec)
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
