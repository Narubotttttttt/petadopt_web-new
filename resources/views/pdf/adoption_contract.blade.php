<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAWS Adoption Contract</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            padding: 28px 32px;
        }

        /* ── HEADER (centered) ──────────────────── */
        .header-wrap {
            text-align: center;
            margin-bottom: 6px;
        }
        .header-inner {
            display: inline-block;
            text-align: left;
        }
        .header-table {
            border-collapse: collapse;
        }
        .logo-cell {
            vertical-align: middle;
            padding-right: 10px;
            width: 75px;
        }
        .logo-cell img {
            width: 70px;
            height: 70px;
        }
        .org-cell {
            vertical-align: middle;
            text-align: center;
        }
        .org-name {
            font-size: 13.5px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .org-details {
            font-size: 9.5px;
            line-height: 1.35;
            text-align: center;
        }

        /* ── TITLE ──────────────────────────────── */
        .contract-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 6px;
            margin: 10px 0 8px 0;
        }

        hr.divider {
            border: 0;
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        /* ── FIELD ROWS ─────────────────────────── */
        .field-row {
            margin-bottom: 5px;
            font-size: 11px;
        }
        .field-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .field-row td {
            vertical-align: bottom;
            padding: 0;
            white-space: nowrap;
        }
        .field-label {
            font-weight: bold;
        }
        .field-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
            padding-bottom: 1px;
        }
        .field-value-wide {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 92%;
            padding-bottom: 1px;
        }

        /* ── AGREEMENT ──────────────────────────── */
        .agreement-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 10px 0 6px 0;
            letter-spacing: 2px;
        }
        .agreement-intro {
            font-size: 11px;
            margin-bottom: 6px;
            line-height: 1.5;
        }
        .agreement-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .agreement-list li {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            font-size: 10.5px;
            line-height: 1.45;
        }
        /* !! Key fix: DejaVu Sans supports Unicode checkmark ✓ */
        .check-cell {
            display: table-cell;
            width: 18px;
            vertical-align: top;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            padding-right: 4px;
        }
        .text-cell {
            display: table-cell;
            vertical-align: top;
        }

        /* ── SIGNATURES ─────────────────────────── */
        .sig-section {
            margin-top: 18px;
        }
        .sig-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-section td {
            text-align: center;
            vertical-align: bottom;
            padding: 0 8px;
            width: 33.3%;
        }
        .sig-box {
            height: 64px;
            border-bottom: 1px solid #000;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-box img {
            max-height: 60px;
            max-width: 180px;
            vertical-align: bottom;
            display: inline-block;
            margin-bottom: 0px;
        }
        .sig-label {
            font-size: 10px;
            text-align: center;
            margin-top: 4px;
            line-height: 1.3;
        }

        /* ── ADOPTER FIELDS ─────────────────────── */
        .adopter-fields {
            margin-top: 12px;
        }
        .adopter-row {
            margin-bottom: 6px;
            font-size: 11px;
        }
        .adopter-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .adopter-row td {
            vertical-align: bottom;
            padding: 0;
        }

        /* ── NOTARY ─────────────────────────────── */
        .notary-section {
            margin-top: 14px;
            font-size: 10.5px;
            line-height: 1.6;
        }
        .notary-sig {
            text-align: center;
            margin-top: 20px;
            float: right;
            width: 200px;
        }
        .notary-sig-line {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-bottom: 3px;
        }
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
</head>
<body>

{{-- ════════════════ HEADER (centered) ════════════════ --}}
<div class="header-wrap">
    <div class="header-inner">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @php
                        $logoPath = public_path('images/caws_logo.jpg');
                        $logoSrc = file_exists($logoPath)
                            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
                            : '';
                    @endphp
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="CAWS Logo" />
                    @endif
                </td>
                <td class="org-cell">
                    <div class="org-name">CDO Animal Welfare Society Inc. (CAWS)</div>
                    <div class="org-details">
                        J Serina St. Carmen, Cagayan de Oro City<br>
                        Tel No: 0936-556-6200 - 0915-99-88880<br>
                        <u>https://www.facebook.com/CdoAnimalRescue/</u>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- ════════════════ TITLE ════════════════ --}}
<div class="contract-title">ADOPTION CONTRACT</div>
<hr class="divider">

{{-- ════════════════ DATE & ID TAG ════════════════ --}}
<div class="field-row">
    <table>
        <tr>
            <td style="width: 38%;">
                <span class="field-label">Date: </span>
                <span class="field-value" style="min-width: 120px;">{{ \Carbon\Carbon::parse($application->scheduled_at ?? $application->approved_at ?? now())->format('F j, Y') }}</span>
            </td>
            <td style="width: 42%;">
                <span class="field-label">Pickup Location: </span>
                <span class="field-value" style="min-width: 140px;">{{ $application->event_location ?: 'CAWS Adoption Event, CDO' }}</span>
            </td>
            <td style="width: 20%; text-align: right;">
                <span class="field-label">ID Tag: </span>
                <span class="field-value" style="min-width: 60px; text-align: center;">Pet no. {{ $pet->id }}</span>
            </td>
        </tr>
    </table>
</div>

{{-- ════════════════ PET'S RECORD ════════════════ --}}
<div class="field-row">
    <span class="field-label">Pet's Record:</span>
    &nbsp;&nbsp;
    Male: <span class="field-value" style="min-width: 30px; text-align: center;">{{ strtolower($pet->gender ?? '') === 'male' ? '/' : '' }}</span>
    &nbsp;&nbsp;
    Female: <span class="field-value" style="min-width: 30px; text-align: center;">{{ strtolower($pet->gender ?? '') === 'female' ? '/' : '' }}</span>
    &nbsp;&nbsp;
    Age on Adoption: <span class="field-value" style="min-width: 55px;">{{ $pet->age ?? '' }}</span>
    &nbsp;&nbsp;
    Color: <span class="field-value" style="min-width: 60px;">{{ $pet->color ?? '' }}</span>
    &nbsp;&nbsp;
    Birthdate: <span class="field-value" style="min-width: 70px;"></span>
</div>

{{-- ════════════════ DESCRIPTION ════════════════ --}}
<div class="field-row">
    <span class="field-label">Description: </span>
    <span class="field-value-wide">{{ $pet->description ?? '' }}</span>
</div>

{{-- ════════════════ BREED ════════════════ --}}
<div class="field-row">
    <span class="field-label">Breed: </span>
    <span>{{ $pet->breed ?? 'Aspin/Puspin' }}</span>
</div>

<hr class="divider">

{{-- ════════════════ ADOPTION AGREEMENT ════════════════ --}}
<div class="agreement-title">ADOPTION AGREEMENT</div>

<div class="agreement-intro">
    <strong>I acknowledge that the CDO Animal Welfare Society Inc. has screened me, and I pass as a RESPONSIBLE ADOPTER.</strong><br>
    I hereby agree to the following:
</div>

<ul class="agreement-list">
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I am the recognized adopter of this rescued pet; the responsibility is mine now after it was rescued by CDO Animal Welfare Society.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree to feed and care for my adopted pet in the best way I could.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree to visit the local vet or City Veterinary Office for any health needs of this rescued pet.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree to facilitate my adopted rescued pet's anti-rabies shot for safety.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I will NEVER hurt or maltreat my adopted rescued pet.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree to inspect and secure my premises so that my adopted pet will not be lost or stolen.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I will handle my adopted rescued pet humanely; never confine them to the cage 24/7 but rather have them free-roam or have adequate walking/out-of-cage time if leashed.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree that my adopted rescued pet will undergo a surgical procedure for Spay and Neuter (Kapon) to help fight unwanted stray/pet overpopulation.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">In any event of natural disaster (i.e., flash flood, earthquakes, etc.) or unforeseen circumstances that require immediate evacuation, I will not abandon my pet in a condition that he/she may not be able to protect themselves.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree not to ABANDON this rescued pet. Or CAWS Team will impose legal sanction under RA 8485 to the said adopter.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree that the ADOPTER IS REQUIRED to report monthly update for pet's health conditions.</span>
    </li>
    <li>
        <span class="check-cell">&#10003;</span>
        <span class="text-cell">I agree that adopting a PET is a lifetime commitment, and promise not to ABANDON OR MALTREAT.</span>
    </li>
</ul>

{{-- ════════════════ SIGNATURES ════════════════ --}}
<div class="sig-section">
    <table>
        <tr>
            <td style="width: 33.3%; vertical-align: bottom;">
                <div class="sig-box">
                    @if(!empty($signatureBase64))
                        <img src="{{ $signatureBase64 }}" alt="Adopter Signature" />
                    @endif
                </div>
                <div class="sig-label">
                    <strong>{{ $adopter->name ?? $application->applicant_name }}</strong><br>
                    Printed Name over Signature of Adopter
                </div>
            </td>
            <td style="width: 33.3%; vertical-align: bottom;">
                <div class="sig-box">
                    @if(!empty($staffSignatureBase64))
                        <img src="{{ $staffSignatureBase64 }}" alt="Staff Signature" />
                    @endif
                </div>
                <div class="sig-label">
                    <strong>{{ $staffName ?? 'CDO Animal Welfare Society Inc.' }}</strong><br>
                    CAWS Authorized Representative
                </div>
            </td>
            <td style="width: 33.3%; vertical-align: bottom;">
                <div class="sig-box"></div>
                <div class="sig-label">Printed Name over<br>Witness</div>
            </td>
        </tr>
    </table>
</div>

{{-- ════════════════ ADOPTER FIELDS ════════════════ --}}
<div class="adopter-fields">
    <div class="adopter-row">
        <table>
            <tr>
                <td style="width: 60%;">
                    <span class="field-label">Adaptor's Name: </span>
                    <span class="field-value" style="min-width: 200px;">{{ $adopter->name }}</span>
                </td>
                <td style="width: 40%;">
                    <span class="field-label">Phone Number: </span>
                    <span class="field-value" style="min-width: 120px;">{{ $adopter->phone_number ?? $application->applicant_phone ?? '' }}</span>
                </td>
            </tr>
        </table>
    </div>
    <div class="adopter-row">
        <span class="field-label">Address: </span>
        <span class="field-value-wide">{{ $adopter->address ?? '' }}</span>
    </div>
    <div class="adopter-row">
        <span class="field-label">Facebook Account: </span>
        <span class="field-value-wide"></span>
    </div>
</div>

{{-- ════════════════ NOTARY ════════════════ --}}
<div class="notary-section clearfix">
    @php
        $notaryDate = ($application && $application->signed_at) ? $application->signed_at : (($application && $application->scheduled_at) ? $application->scheduled_at : now());
        $notaryLocation = !empty($application->event_location) ? $application->event_location : 'Cagayan de Oro City, Philippines';
    @endphp
    <p>
        SUBSCRIBED AND SWORN TO before me this <strong><u>{{ $notaryDate->format('jS') }}</u></strong> day of <strong><u>{{ $notaryDate->format('F') }}</u></strong>, <strong><u>{{ $notaryDate->format('Y') }}</u></strong> at <strong><u>{{ $notaryLocation }}</u></strong>,
        affiant exhibited to me his/her valid identification and residence credentials on record.
    </p>
    <div class="notary-sig">
        <div class="notary-sig-line"></div>
        <div style="font-size: 10.5px;">Notary Public</div>
    </div>
</div>

</body>
</html>
