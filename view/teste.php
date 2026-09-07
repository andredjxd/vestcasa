<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste CKFinder</title>
    
    <script src="../assets/_php/libs/ckfinder/ckfinder.js"></script>
</head>
<body>

    <h2>Testando CKFinder</h2>

    <input type="text" id="fileInput" placeholder="Selecione uma imagem..." readonly>
    <button onclick="openCKFinder()">Escolher Imagem</button>
    <br><br>
    <img id="preview" src="" alt="Pré-visualização" style="max-width: 300px; display: none;">

    <script>
        function openCKFinder() {
            CKFinder.popup({
                chooseFiles: true,
                onInit: function (finder) {
                    finder.on('files:choose', function (evt) {
                        var file = evt.data.files.first();
                        var url = file.getUrl();
                        document.getElementById('fileInput').value = url;
                        document.getElementById('preview').src = url;
                        document.getElementById('preview').style.display = "block";
                    });

                    finder.on('file:choose:resizedImage', function (evt) {
                        var url = evt.data.resizedUrl;
                        document.getElementById('fileInput').value = url;
                        document.getElementById('preview').src = url;
                        document.getElementById('preview').style.display = "block";
                    });
                }
            });
        }
    </script>

</body>
</html>
