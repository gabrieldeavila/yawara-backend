<html>

<head>
    <style>
        * {
            font-family: "Montserrat", sans-serif;
        }

        h1 {
            font-weight: 800;
            color: #d8c89b;
        }

        .container {
            width: 100%;
            height: fit-content;
            background: #222222;
            border-radius: 0.5rem;
        }

        .content {
            color: white;
        }

        h2 {
            margin-top: 1rem;
            text-align: center;
            margin-bottom: 3rem;
        }

        .btn {
            text-decoration: none;
            font-weight: 600;
            font-size: 20px;
            border-radius: 0.5rem;
            text-align: center;
            color: white;
            background-color: #819aa8;
            width: 100%;
            display: block;
            margin-top: 3rem;
            cursor: pointer;
            transition: 0.2s ease-in-out;
        }

        .copy-wrapper {
            padding: 1rem;
        }

        .btn:hover {
            background-color: #d8c89b;
        }

        .copy {
            text-align: center;
            color: #37aff1;
            display: block;
        }

        .browser {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin-top: 3rem;
        }

    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
</head>

<body>
    <div class="container">
        <div class="mutate" style="padding: 1rem">
            <h1 style="text-align: center">Yawara</h1>
            <div class="content">
                <h2>Recuperar Senha</h2>
                <p style="text-align: center">Por favor, clique no botão abaixo para criar uma nova senha.</p>
                <a style="color: white" class="btn"
                    href="{{ env('FRONT_URL') . 'password-reset/' . $token }}">
                    <div class="copy-wrapper">
                        Criar Nova Senha
                    </div>
                </a>
                <p class="browser">Ou copie e cole esse link no seu navegador:</p>
                <a class="copy" href="{{ env('FRONT_URL') . 'password-reset/' . $token }}">
                    {{ env('FRONT_URL') . 'password-reset/' . $token }}
                </a>
            </div>
        </div>
    </div>
</body>

</html>
