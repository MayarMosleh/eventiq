<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #e2e2e2;
            border-radius: 8px;
            padding: 30px;
            max-width: 500px;
            margin: auto;
        }
        .header {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #1e88e5;
            text-align: center;
            letter-spacing: 3px;
            margin: 20px 0;
        }
        .footer {
            font-size: 14px;
            text-align: center;
            color: #777;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">Your Verification Code</div>

    <p style="text-align: center;">
        Please use the following verification code to complete your email verification:
    </p>

    <div class="code">{{ $code }}</div>

    <p style="text-align: center;">
        This code is valid for <strong>2 minutes</strong> only.
    </p>

    <div class="footer">
        If you did not request this code, you can safely ignore this message.
    </div>
</div>
</body>
</html>
