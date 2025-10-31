<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment {{ ucfirst($status) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: {{ $status === 'success' ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' }};
        }
        .container {
            text-align: center;
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 400px;
            margin: 1rem;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        h1 {
            color: #333;
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
        }
        p {
            color: #666;
            margin: 0 0 1.5rem 0;
            line-height: 1.6;
        }
        .spinner {
            margin: 1.5rem auto;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid {{ $status === 'success' ? '#667eea' : '#f5576c' }};
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #666;
        }
        .manual-link {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fff3cd;
            border-radius: 8px;
            border: 1px solid #ffc107;
        }
        .manual-link a {
            color: #856404;
            text-decoration: none;
            font-weight: 600;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            @if($status === 'success')
                ✅
            @else
                ❌
            @endif
        </div>
        
        <h1>Payment {{ ucfirst($status) }}</h1>
        
        @if($status === 'success')
            <p>Your payment has been processed successfully. Redirecting you back to the app...</p>
        @else
            <p>Payment was not completed. Redirecting you back to the app...</p>
        @endif
        
        <div class="spinner"></div>
        
        @if($transaction_id || $source_id)
            <div class="details">
                @if($transaction_id)
                    <strong>Transaction ID:</strong> {{ $transaction_id }}<br>
                @endif
                @if($source_id)
                    <strong>Source ID:</strong> {{ $source_id }}
                @endif
            </div>
        @endif

        <div class="manual-link" id="manual-link" style="display: none;">
            <p style="margin: 0 0 0.5rem 0; font-size: 0.9rem;">If you're not redirected automatically:</p>
            <a href="#" id="deep-link">Tap here to open the app</a>
        </div>
    </div>

    <script>
        // Build deep link URL
        const status = '{{ $status }}';
        const transactionId = '{{ $transaction_id ?? '' }}';
        const sourceId = '{{ $source_id ?? '' }}';
        
        let deepLink = `laundryapp://payment/${status}`;
        const params = [];
        
        if (transactionId) params.push(`transaction_id=${transactionId}`);
        if (sourceId) params.push(`source_id=${sourceId}`);
        
        if (params.length > 0) {
            deepLink += '?' + params.join('&');
        }
        
        // Set manual link
        document.getElementById('deep-link').href = deepLink;
        
        // Auto-redirect after 1 second
        setTimeout(() => {
            window.location.href = deepLink;
        }, 1000);
        
        // Show manual link after 3 seconds if redirect fails
        setTimeout(() => {
            document.getElementById('manual-link').style.display = 'block';
        }, 3000);
    </script>
</body>
</html>
