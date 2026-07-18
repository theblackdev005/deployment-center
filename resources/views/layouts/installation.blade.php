<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Installation - {{ config('app.name', 'Deploy Center') }}</title>
        <style>
            * { box-sizing: border-box; }
            body { margin: 0; background: #f4f5f7; color: #0f172a; font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            .shell { width: min(920px, calc(100% - 28px)); margin: 32px auto; }
            .panel { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 8px 28px rgba(15, 23, 42, .07); }
            header { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 28px 32px; color: #fff; background: #1e293b; }
            header h1 { margin: 0; font-size: 28px; } header p { margin: 6px 0 0; color: #cbd5e1; }
            .mark { display: grid; width: 48px; height: 48px; place-items: center; flex: 0 0 auto; border-radius: 7px; background: #673de6; font-weight: 800; }
            .head-copy { display: flex; align-items: center; gap: 16px; }
            .content { padding: 30px 32px 34px; }
            h2 { margin: 0; font-size: 22px; } h3 { margin: 28px 0 4px; font-size: 17px; }
            .lead, .hint { color: #64748b; line-height: 1.55; } .lead { margin: 7px 0 24px; } .hint { display: block; margin-top: 6px; font-size: 13px; }
            .notice, .errors { margin-bottom: 22px; padding: 13px 15px; border-left: 4px solid; }
            .notice { border-color: #059669; background: #ecfdf5; color: #065f46; }
            .errors { border-color: #dc2626; background: #fef2f2; color: #991b1b; } .errors ul { margin: 0; padding-left: 20px; }
            .requirements { width: 100%; margin: 18px 0 26px; border-collapse: collapse; font-size: 14px; }
            .requirements td { padding: 11px 12px; border-bottom: 1px solid #e2e8f0; } .requirements td:last-child { text-align: right; font-weight: 700; }
            .ready { color: #047857; } .missing { color: #b91c1c; }
            .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 22px; }
            .full { grid-column: 1 / -1; } label { display: block; margin-bottom: 7px; color: #334155; font-size: 14px; font-weight: 700; }
            input, select { width: 100%; min-height: 46px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #0f172a; font: inherit; }
            input:focus, select:focus { outline: 3px solid rgba(103, 61, 230, .14); border-color: #8062e8; }
            .choice { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
            .choice label { display: flex; align-items: flex-start; gap: 10px; min-height: 74px; margin: 0; padding: 14px; border: 1px solid #cbd5e1; border-radius: 7px; cursor: pointer; }
            .choice input { width: 18px; min-height: 18px; margin-top: 1px; } .choice strong, .choice span { display: block; } .choice span { margin-top: 3px; color: #64748b; font-size: 13px; font-weight: 400; }
            .visuals { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; }
            .preview { display: grid; height: 120px; margin-bottom: 10px; place-items: center; overflow: hidden; border: 1px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; color: #94a3b8; }
            .preview img { max-width: 80%; max-height: 92px; object-fit: contain; }
            .actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 30px; padding-top: 22px; border-top: 1px solid #e2e8f0; }
            button, .button { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; padding: 10px 18px; border: 1px solid transparent; border-radius: 6px; background: #673de6; color: #fff; font: inherit; font-weight: 700; cursor: pointer; text-decoration: none; }
            button:hover { background: #5530c9; } button.secondary { border-color: #cbd5e1; background: #fff; color: #475569; }
            button:disabled { cursor: not-allowed; opacity: .5; }
            @media (max-width: 680px) { .shell { width: 100%; margin: 0; } .panel { min-height: 100vh; border: 0; border-radius: 0; } header, .content { padding: 22px 20px; } header h1 { font-size: 23px; } .grid, .choice, .visuals { grid-template-columns: 1fr; } .full { grid-column: auto; } .actions { align-items: stretch; flex-direction: column-reverse; } .actions button { width: 100%; } }
        </style>
    </head>
    <body>@yield('content')</body>
</html>
