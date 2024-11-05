<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gudang API</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: none;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 0% 0%, rgba(255,0,0,0.5), rgba(255,255,255,0));
            z-index: -1;
        }

        .background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 15% 20%, rgba(255,0,0,0.5), rgba(255,255,255,0));
        }

        .background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 25% 40%, rgba(255,0,0,0.3), rgba(255,255,255,0));
        }

        .center-text {
            position: relative;
            z-index: 1;
            font-size: 48px;
            color: #FFF;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="background"></div>
    <div class="center-text">Gudang API</div>

</body>
</html>
