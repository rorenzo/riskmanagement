<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Scheda Anagrafica - {{ $profile->cognome }} {{ $profile->nome }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; font-size: 10pt; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; }
        .footer { font-size: 8pt; position: fixed; bottom: 0; width:100%; text-align: center; }
        
        .card { 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            margin-bottom: 15px; 
            page-break-inside: avoid; /* Cerca di non spezzare la card tra le pagine */
        }
        .card-header { 
            background-color: #f5f5f5; 
            padding: 8px 12px; 
            font-weight: bold; 
            border-bottom: 1px solid #ddd;
            font-size: 11pt;
        }
        .card-body { padding: 12px; }
        .card-body p { margin: 0 0 8px 0; line-height: 1.4; }
        .card-body strong { /* color: #555; */ } /* Rimosso per miglior contrasto */
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #eee; padding: 6px; text-align: left; font-size: 9pt; }
        th { background-color: #f9f9f9; font-weight: bold; }
        
        .list-group { list-style: none; padding-left: 0; }
        .list-group-item { padding: 5px 0; border-bottom: 1px dashed #eee; }
        .list-group-item:last-child { border-bottom: none; }

        .text-muted { color: #777; }
        .signature-section { margin-top: 50px; page-break-inside: avoid; }
        .signature-line { border-bottom: 1px solid #333; width: 250px; margin-top: 40px; }
        .date-place { margin-top: 20px; text-align: right; }

        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; } /* Non supportato da DOMPDF */
        .row::after { content: ""; clear: both; display: table; } /* Clearfix per float */
        .col-6 { width: 48%; float: left; margin-right: 2%; } /* Semplice layout a due colonne con float */
        .col-6:last-child { margin-right: 0; }
        .clearfix::after {  content: ""; clear: both; display: table; }

        /* New style for logo */
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px; /* Adjust as needed */
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Logo Section --}}
        <div class="logo">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo">
        </div>

        <div class="header">
            <h1>SCHEDA ANAGRAFICA E DI SICUREZZA</h1>
            <h2>{{ $profile->grado ? $profile->grado . ' ' : '' }}{{ $profile->cognome }} {{ $profile->nome }}</h2>
        </div>

        {{-- Card Dati Anagrafici --}}
        <div class="card">
            <div class="card-header">Dati Anagrafici</div>
            <div class="card-body">
                <div class="row clearfix">
                    <div class="col-6">
                        <p><strong>Grado:</strong> {{ $profile->grado ?? 'N/D' }}</p>
                        <p><strong>Nome:</strong> {{ $profile->nome }}</p>
                        <p><strong>Cognome:</strong> {{ $profile->cognome }}</p>
                        <p><strong>Sesso:</strong> {{ $profile->sesso ?? 'N/D' }}</p>
                    </div>
                    <div class="col-6">
                        <p><strong>Data di Nascita:</strong> {{ $profile->data_nascita ? $profile->data_nascita->format('d/m/Y') : 'N/D' }}</p>
                        <p><strong>Luogo di Nascita:</strong> 
                            {{ $profile->luogo_nascita_citta ?? '' }}
                            {{ $profile->luogo_nascita_provincia ? ' (' . $profile->luogo_nascita_provincia . ')' : '' }}
                            <span class="text-muted">({{ $profile->luogo_nascita_nazione ?? 'Italia' }})</span>
                        </p>
                        <p><strong>Codice Fiscale:</strong> {{ $profile->cf ?? 'N/D' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Recapiti --}}
        <div class="card">
            <div class="card-header">Recapiti</div>
            <div class="card-body">
                <p><strong>Email:</strong> {{ $profile->email ?? 'N/D' }}</p>
                <p><strong>Cellulare:</strong> {{ $profile->cellulare ?? 'N/D' }}</p>
                <p><strong>Residenza:</strong> 
                    {{ $profile->residenza_via ?? 'N/D' }},
                    {{ $profile->residenza_citta ?? '' }}
                    {{ $profile->residenza_provincia ? ' (' . $profile->residenza_provincia . ')' : '' }}
                    {{ $profile->residenza_cap ? ' - ' . $profile->residenza_cap : '' }}
                    <span class="text-muted">({{ $profile->residenza_nazione ?? 'Italia' }})</span>
                </p>
            </div>
        </div>

        {{-- Card Posizione Lavorativa --}}
        <div class="card">
            <div class="card-header">Posizione Lavorativa Attuale</div>
            <div class="card-body">
                @if ($currentEmploymentPeriod)
                    <p><strong>Data Arrivo (inizio impiego):</strong> {{ $currentEmploymentPeriod->data_inizio_periodo->format('d/m/Y') }}</p>
                    @if ($currentSection && $currentSection->office)
                        <p><strong>Ufficio:</strong> {{ $currentSection->office->nome ?? 'N/D' }}</p>
                    @else
                           <p><strong>Ufficio:</strong> {{ $profile->sectionHistory->first()->office->nome ?? 'Non assegnato a Ufficio / Sezione' }}</p>
                    @endif
                    <p><strong>Sezione:</strong> {{ $currentSection->nome ?? 'Non assegnato a Sezione' }}</p>
                    <p><strong>Incarico Organizzativo:</strong> {{ $currentEmploymentPeriod->incarico_display_name ?? 'N/D' }}</p>
                    <p><strong>Mansione S.P.P.:</strong> {{ $currentEmploymentPeriod->mansione_spp_display_name ?? 'N/D' }}</p>
                @else
                    <p class="text-muted">Nessun periodo di impiego attualmente attivo.</p>
                @endif
            </div>
        </div>

        {{-- Card Attività Assegnate --}}
        <div class="card">
            <div class="card-header">Attività Assegnate</div>
            <div class="card-body">
                @if($profile->activities->isNotEmpty())
                    <ul class="list-group">
                        @foreach($profile->activities as $activity)
                            <li class="list-group-item">{{ $activity->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Nessuna attività specifica assegnata.</p>
                @endif
            </div>
        </div>

        {{-- Card Rischi Connessi --}}
        <div class="card">
            <div class="card-header">Rischi Connessi (derivati dalle attività)</div>
            <div class="card-body">
                @if($connectedRisks->isNotEmpty())
                    <ul class="list-group">
                        @foreach($connectedRisks as $risk)
                            <li class="list-group-item">{{ $risk->name }} <span class="text-muted">- Tipologia: {{ $risk->tipologia ?? 'N/D' }}</span></li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Nessun rischio specifico identificato per le attività assegnate.</p>
                @endif
            </div>
        </div>

        {{-- Card DPI Assegnati/Necessari --}}
        <div class="card">
            <div class="card-header">DPI Assegnati / Necessari</div>
            <div class="card-body">
                    @if($allPpesForProfile->isNotEmpty())
                        <ul class="list-group">
                            @foreach($allPpesForProfile as $ppe)
                                <li class="list-group-item">{{ $ppe->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Nessun DPI assegnato o specificamente richiesto dai rischi delle attività.</p>
                    @endif
            </div>
        </div>

        {{-- Card Sorveglianza Sanitaria --}}
        <div class="card">
            <div class="card-header">Sorveglianza Sanitaria Richiesta</div>
            <div class="card-body">
                @if($requiredHealthSurveillances->isNotEmpty())
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo Visita</th>
                                <th>Cadenza (Anni)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requiredHealthSurveillances as $hs)
                            <tr>
                                <td>{{ $hs->name }}</td>
                                <td>{{ $hs->duration_years ?? 'Non specificata' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Nessuna sorveglianza sanitaria specifica richiesta dalle attività assegnate.</p>
                @endif
            </div>
        </div>
        
        <div class="date-place">
            <p>{{ $generationPlace ?? 'Taranto' }}, {{ $generationDate ?? '' }}</p>
        </div>

        <div class="signature-section">
            <p>Firma del Lavoratore (per presa visione)</p>
            <div class="signature-line"></div>
            <br><br>
            <p>Firma del Datore di Lavoro / Responsabile</p>
            <div class="signature-line"></div>
        </div>

    </div>
    {{--
    <div class="footer">
        Documento generato da {{ config('app.name', 'Applicativo Gestione Rischi') }} - Pagina {PAGE_NUM} di {PAGE_COUNT}
    </div>
    --}}
</body>
</html>