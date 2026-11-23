<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAWS - {{ $reportType === 'intakes' ? 'Pet Inventory' : ucfirst($reportType) }} Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            padding: 24px 28px;
            line-height: 1.4;
        }

        /* Header */
        .header-wrap {
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #199CA4;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header-table {
            border-collapse: collapse;
            margin: 0 auto;
        }
        .logo-cell {
            width: 58px;
            vertical-align: middle;
            padding-right: 12px;
            text-align: right;
        }
        .logo-cell img {
            width: 52px;
            height: 52px;
        }
        .org-cell {
            vertical-align: middle;
            text-align: center;
        }
        .org-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .org-sub {
            font-size: 9px;
            color: #64748b;
        }

        /* Report Title & Metadata */
        .report-title-bar {
            margin-top: 10px;
            margin-bottom: 12px;
        }
        .report-title {
            font-size: 15px;
            font-weight: bold;
            color: #199CA4;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-table td {
            padding: 5px 8px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 18%;
        }
        .meta-value {
            color: #0f172a;
        }

        /* Section Headings */
        .section-heading {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin-top: 14px;
            margin-bottom: 6px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
        }

        /* Summary Stats Cards Grid */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        .stats-table td {
            padding: 8px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .stat-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .stat-sub {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Data Records Table */
        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .records-table th {
            background-color: #199CA4;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #148087;
            text-align: left;
        }
        .records-table td {
            padding: 5px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
            vertical-align: top;
        }
        .records-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Status Text (Clean and tag-free for PDF print) */
        .status-plain {
            font-size: 8.5px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Sign-off certification */
        .signoff-wrap {
            margin-top: 24px;
            width: 100%;
            border-collapse: collapse;
        }
        .signoff-wrap td {
            vertical-align: top;
            padding: 6px 12px;
        }
        .sign-line {
            margin-top: 36px;
            border-top: 1px solid #000000;
            text-align: center;
            padding-top: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .sign-title {
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- Official Header (Centered) --}}
    <div class="header-wrap">
        <table align="center" class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="CAWS Logo">
                    @endif
                </td>
                <td class="org-cell">
                    <div class="org-name">CDO Animal Welfare Society Inc.</div>
                    <div class="org-sub">Non-Profit Pet Rescue, Rehabilitation & Adoption Shelter | Cagayan de Oro City, Philippines</div>
                    <div class="org-sub">Official Administrative & Compliance Report</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Report Title & Meta --}}
    <div class="report-title-bar">
        <div class="report-title">{{ $reportType === 'intakes' ? 'Pet Inventory' : ($reportType === 'adoptions' ? 'Finalized Adoptions' : ucfirst($reportType)) }} Report & Analytics Summary</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Reporting Period:</td>
            <td class="meta-value">{{ $dateRangeLabel }}</td>
            <td class="meta-label">Generated On:</td>
            <td class="meta-value">{{ $generatedAt }}</td>
        </tr>
        <tr>
            <td class="meta-label">Generated By:</td>
            <td class="meta-value">{{ $generatedByName }} ({{ $generatedByRole }})</td>
            <td class="meta-label">Species Scope:</td>
            <td class="meta-value">{{ ucfirst($species) }}</td>
        </tr>
    </table>

    {{-- KPI Summary Stats --}}
    <div class="section-heading">Key Performance Indicators</div>
    <table class="stats-table">
        <tr>
            @if($reportType === 'adoptions')
                <td>
                    <div class="stat-label">Finalized Adoptions</div>
                    <div class="stat-value">{{ $stats['finalizedCount'] ?? $stats['approvedCount'] ?? 0 }}</div>
                    <div class="stat-sub">Completed Handover</div>
                </td>
                <td>
                    <div class="stat-label">Total Applications</div>
                    <div class="stat-value">{{ $stats['totalApplications'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['approvalRate'] ?? 0 }}% Conversion</div>
                </td>
                <td>
                    <div class="stat-label">In Evaluation</div>
                    <div class="stat-value">{{ ($stats['underReviewCount'] ?? 0) + ($stats['pendingCount'] ?? 0) }}</div>
                    <div class="stat-sub">Pending / Review</div>
                </td>
                <td>
                    <div class="stat-label">Ineligible</div>
                    <div class="stat-value">{{ $stats['rejectedCount'] ?? 0 }}</div>
                    <div class="stat-sub">Rejected</div>
                </td>
            @elseif($reportType === 'intakes')
                <td>
                    <div class="stat-label">Live Shelter Census</div>
                    <div class="stat-value">{{ $stats['currentLiveCensus'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['liveDogs'] ?? 0 }} Dogs, {{ $stats['liveCats'] ?? 0 }} Cats</div>
                </td>
                <td>
                    <div class="stat-label">Avg Length of Stay</div>
                    <div class="stat-value">{{ $stats['avgDaysInShelter'] ?? 0 }} Days</div>
                    <div class="stat-sub">{{ $stats['longStayCount'] ?? 0 }} Long-Stay (&ge;60d)</div>
                </td>
                <td>
                    <div class="stat-label">Period Rescues</div>
                    <div class="stat-value">{{ $stats['totalIntakes'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['dogCount'] ?? 0 }} Dogs, {{ $stats['catCount'] ?? 0 }} Cats</div>
                </td>
                <td>
                    <div class="stat-label">Medical Procedures</div>
                    <div class="stat-value">{{ $stats['totalMedicalProcedures'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['vaccinationCount'] ?? 0 }} Vax &bull; {{ $stats['surgeryCount'] ?? 0 }} Surg</div>
                </td>
            @else
                <td style="width: 25%;">
                    <div class="stat-label">Period Rescues</div>
                    <div class="stat-value">{{ $stats['totalIntakes'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['dogIntakes'] ?? 0 }} Dogs, {{ $stats['catIntakes'] ?? 0 }} Cats</div>
                </td>
                <td style="width: 25%;">
                    <div class="stat-label">Live Shelter Census</div>
                    <div class="stat-value">{{ $stats['currentLiveCensus'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['inShelterCount'] ?? 0 }} Care &bull; {{ $stats['availableCount'] ?? 0 }} Ready</div>
                </td>
                <td style="width: 25%;">
                    <div class="stat-label">Adoptions</div>
                    <div class="stat-value">{{ $stats['approvedAdoptions'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['conversionRate'] ?? 0 }}% Conversion</div>
                </td>
                <td style="width: 25%;">
                    <div class="stat-label">Medical Procedures</div>
                    <div class="stat-value">{{ $stats['totalMedicalProcedures'] ?? 0 }}</div>
                    <div class="stat-sub">{{ $stats['vaccinationCount'] ?? 0 }} Vax &bull; {{ $stats['surgeryCount'] ?? 0 }} Surg</div>
                </td>
            @endif
        </tr>
    </table>

    {{-- Detailed Records --}}
    <div class="section-heading">{{ in_array($reportType, ['adoptions', 'overview']) ? 'Finalized Adoption Registry' : 'Detailed Registry Records' }} ({{ count($records) }} {{ Str::plural('Record', count($records)) }})</div>
    <table class="records-table">
        @if($reportType === 'adoptions')
            <thead>
                <tr>
                    <th style="width: 12%;">App ID</th>
                    <th style="width: 26%;">Adopter</th>
                    <th style="width: 22%;">Pet Adopted</th>
                    <th style="width: 12%;">Species</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 16%;">Finalized Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $app)
                    <tr>
                        <td>#APP-{{ str_pad($app->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <strong>{{ $app->applicant_name }}</strong><br>
                            <span style="color: #64748b;">{{ $app->applicant_email }}</span>
                        </td>
                        <td><strong>{{ $app->pet ? $app->pet->name : 'Pet no. ' . $app->pet_id }}</strong></td>
                        <td style="text-transform: capitalize;">{{ $app->pet ? ($app->pet->type ?? 'N/A') : 'N/A' }}</td>
                        <td>
                            <strong style="color: #0f172a;">Finalized</strong>
                        </td>
                        <td>{{ ($app->documents_verified_at ?? $app->approved_at ?? $app->created_at)?->format('M d, Y') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 12px;">No finalized adoptions found in this period.</td></tr>
                @endforelse
            </tbody>

        @elseif($reportType === 'intakes')
            <thead>
                <tr>
                    <th style="width: 10%;">Pet ID</th>
                    <th style="width: 18%;">Name & Species</th>
                    <th style="width: 14%;">Inventory Status</th>
                    <th style="width: 12%;">Length of Stay</th>
                    <th style="width: 22%;">Rescue Origin & Date</th>
                    <th style="width: 24%;">Intake Condition</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $pet)
                    <tr>
                        <td>#PET-{{ str_pad($pet->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <strong>{{ $pet->name ?? 'Pet no. ' . $pet->id }}</strong><br>
                            <span style="color: #64748b; text-transform: capitalize;">{{ $pet->type ?? 'N/A' }} &bull; {{ $pet->breed ?? 'Mixed' }}</span>
                        </td>
                        <td>
                            <strong style="color: #0f172a;">
                                @if($pet->status === 'in_shelter')
                                    In Shelter
                                @elseif($pet->status === 'available')
                                    Available
                                @elseif($pet->status === 'pending')
                                    Pending
                                @elseif($pet->status === 'adopted')
                                    Adopted
                                @elseif($pet->status === 'deceased')
                                    Deceased
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $pet->status ?? 'N/A')) }}
                                @endif
                            </strong>
                        </td>
                        <td>
                            <strong>{{ $pet->days_in_shelter ?? 0 }} days</strong>
                            @if(($pet->days_in_shelter ?? 0) >= 60 && in_array($pet->status, ['in_shelter', 'available', 'pending']))
                                <br><span style="color: #64748b; font-size: 7px; font-weight: bold;">(Long stay &ge;60d)</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ ($pet->rescue_date ?? $pet->created_at)?->format('M d, Y') ?? 'N/A' }}</strong><br>
                            <span style="color: #64748b;">{{ $pet->rescue_location ?? 'Not specified' }}</span>
                        </td>
                        <td>{{ Str::limit($pet->intake_condition ?? 'Healthy / Normal condition', 55) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 12px;">No pet inventory records found.</td></tr>
                @endforelse
            </tbody>

        @else
            <thead>
                <tr>
                    <th style="width: 12%; text-align: center;">Adoption</th>
                    <th style="width: 30%;">Adopter</th>
                    <th style="width: 24%;">Pet Adopted</th>
                    <th style="width: 17%;">Finalized Date</th>
                    <th style="width: 17%;">Authorized Staff</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $app)
                    <tr>
                        <td style="text-align: center; font-weight: bold; color: #0f172a;">{{ $loop->iteration }}.</td>
                        <td>
                            <strong>{{ $app->applicant_name }}</strong><br>
                            <span style="color: #64748b;">{{ $app->applicant_email }}</span>
                        </td>
                        <td><strong>{{ $app->pet ? $app->pet->name : 'Pet no. ' . $app->pet_id }}</strong></td>
                        <td>{{ ($app->approved_at ?? $app->documents_verified_at ?? $app->updated_at) ? \Carbon\Carbon::parse($app->approved_at ?? $app->documents_verified_at ?? $app->updated_at)->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $app->staff_name ?? ($app->staff ? $app->staff->name : 'CAWS Representative') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 12px;">No finalized adoptions in this period.</td></tr>
                @endforelse
            </tbody>
        @endif
    </table>

    {{-- Official Sign-Off Section --}}
    <table class="signoff-wrap">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                @if(!empty($signatureBase64))
                    <div style="text-align: center; margin-bottom: -15px;">
                        <img src="{{ $signatureBase64 }}" style="max-height: 40px; display: inline-block;">
                    </div>
                @endif
                <div class="sign-line">{{ $generatedByName }}</div>
                <div class="sign-title">Authorized Signatory / Shelter Administrator</div>
                <div class="sign-title" style="margin-top: 1px;">CDO Animal Welfare Society Inc.</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        This document is an official administrative report of CDO Animal Welfare Society Inc. Generated automatically by CAWS Pet Adoption & Shelter Management System.
    </div>

</body>
</html>
