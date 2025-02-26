<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Share Bus Position</title>
    <meta name="description" content="Share Bus Position">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="https://nginovasi.com/asset/img/Logo%20NGI.svg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
        }

        .header {
            background-color: white;
            color: white;
            padding: 20px;
            align-items: center;
        }

        .header i {
            font-size: 1.5em;
            margin-right: 20px;
        }

        .content {
            padding: 20px;
            text-align: center;
        }

        .content h1 {
            font-size: 24px;
            color: #0275d8;
            /* color: #ffc115; */
            text-shadow: 2px 2px #252525;
        }

        .content p {
            font-weight: bold;
            color: white;
            font-size: 14px;
            margin-bottom: 30px;
            text-shadow: 2px 2px #252525;

        }

        .form-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .form-control {
            max-width: 60px;
            text-align: center;
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #0d6efd;
            border: none;
        }

        input[type="text"] {
            text-transform: uppercase;
        }

        body {
            background-image: url("<?= base_url() ?>/public/img/bus_bg.jpeg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            width: 430px;
        }


    </style>
</head>
<body>

    <div class="header">
        <center>
            <img src="https://nginovasi.com/asset/img/Logo%20NGI.svg" style="width: 15%">
        </center>
    </div>

    <div class="content">
        <div style="margin-top: 50%;"></div>
        <h1 style="font-weight: bold;">LACAK <span style="color: #ffc107;">BUS</span></h1>
        <p>Nih, kamu bisa lacak busnya langsung (real-time), jadi perjalanannya lebih tenang dan enak. Kapan aja dan di mana aja, kamu bisa tau posisi, jalurnya, sama kapan busnya bakal dateng.</p>

        <div class="form-group">
            <input placeholder="S" type="text" class="form-control" style="max-width: 25%; line-height: 2.0;" max="2" id="inputFirst" autocomplete="off">
            <input placeholder="1234" type="number" class="form-control" style="max-width: 50%; line-height: 2.0;" max="4" id="inputTwo" autocomplete="off">
            <input placeholder="MR" type="text" class="form-control" style="max-width: 25%; line-height: 2.0;" max="2" id="inputThree" autocomplete="off">
        </div>

        <button class="btn btn-primary" type="button" onclick="onTrack()">LACAK SEKARANG</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        async function onTrack() {
            let first = document.getElementById("inputFirst").value;
            let two = document.getElementById("inputTwo").value;
            let three = document.getElementById("inputThree").value;
            if(first == '') {
                return;
            }
            if(two == '') {
                return;
            }
            if(three == '') {
                return;
            }
            let param = first+two+three;
            window.location.href = "<?= base_url() ?>/lacak_bus?plate="+param+"";
        }
    </script>
</body>
</html>
