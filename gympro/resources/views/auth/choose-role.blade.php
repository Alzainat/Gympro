<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Choose Login Type</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 14px;
            width: 340px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,.2);
        }

        h2 {
            margin-bottom: 10px;
        }

        p {
            color: #64748b;
            margin-bottom: 30px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border-radius: 10px;
            font-size: 16px;
            text-decoration: none;
            font-weight: bold;
            transition: .3s;
        }

        .admin {
            background: #f59e0b;
            color: white;
        }

        .trainer {
            background: #6366f1;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: .9;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Welcome 👋</h2>
    <p>Please choose how you want to login</p>

    <a href="{{ url('/admin/login') }}" class="btn admin">
        Login as Admin
    </a>

    <a href="{{ url('/trainer/login') }}" class="btn trainer">
        Login as Trainer
    </a>
</div>

</body>
</html>