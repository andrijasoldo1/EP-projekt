<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potpisivanje dokumenta</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        canvas { border: 2px solid black; background: white; }
    </style>
</head>
<body>

    <h2>Potpišite dokument</h2>
    <canvas id="signature-pad" width="400" height="200"></canvas>
    <br>
    <button id="clear-signature">Obriši potpis</button>
    <button id="save-signature">Spremi potpis</button>

    <script>
        var canvas = document.getElementById("signature-pad");
        var ctx = canvas.getContext("2d");
        var isDrawing = false;

        canvas.addEventListener("mousedown", function(event) {
            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(event.offsetX, event.offsetY);
        });

        canvas.addEventListener("mousemove", function(event) {
            if (isDrawing) {
                ctx.lineTo(event.offsetX, event.offsetY);
                ctx.stroke();
            }
        });

        canvas.addEventListener("mouseup", function() {
            isDrawing = false;
        });

        document.getElementById("clear-signature").addEventListener("click", function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        document.getElementById("save-signature").addEventListener("click", function() {
            var signatureData = canvas.toDataURL("image/png");

            fetch("{{ route('saveSignature') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ signature: signatureData })
            }).then(response => response.json())
              .then(data => alert(data.message));
        });
    </script>

</body>
</html>
