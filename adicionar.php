<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Imagem - ReddArt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/adicionar.css">
</head>
<body>

    <!-- Fundo dinâmico com borrado (blur) -->
    <div id="bgBlur" class="bg-blur-overlay"></div>

    <div class="upload-card">
        <h2><i class="fa-solid fa-wand-magic-sparkles"></i> Enviar Nova Arte</h2>

        <form action="processar_upload.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="titulo">Título da Imagem</label>
                <input type="text" id="titulo" name="titulo" placeholder="Ex: Ilustração Cyberpunk" required>
            </div>

           <div class="form-group">
            <label for="categoria">Categoria</label>
                <select name="categoria" id="categoria" required>
                 <option value="" disabled selected>Selecione uma categoria...</option>
                 <option value="1">Animes</option>
                 <option value="2">Jogos</option>
                 <option value="3">Fan-Art</option>
                 <option value="4">Filme</option>
                 <option value="5">Shitpost</option>
                 <option value="6">Esportes</option>
                 <option value="7">Livros</option>
                 <option value="8">Mangá</option>
                 <option value="9">Cartoons</option>
                 <option value="10">Moda</option>
                 <option value="11">Paisagem</option>
                 <option value="12">Viagens</option>
            </select>
           </div>

            <div class="form-group">
                <label>Arquivo da Arte</label>
                <div class="file-upload-box">
                    <input type="file" id="imagem" name="imagem" accept="image/*" required onchange="mostrarPreview(this)">
                    
                    <div class="upload-content" id="uploadContent">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p><span>Clique para selecionar</span> ou arraste a imagem</p>
                    </div>

                    <div class="preview-container" id="previewContainer">
                        <img id="imgPreview" src="" alt="Pré-visualização">
                        <div class="preview-overlay">
                            <i class="fa-solid fa-pen"></i> Trocar imagem
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">Publicar Imagem</button>

        </form>
    </div>

    <script>
        function mostrarPreview(input) {
            const previewContainer = document.getElementById('previewContainer');
            const imgPreview = document.getElementById('imgPreview');
            const uploadContent = document.getElementById('uploadContent');
            const bgBlur = document.getElementById('bgBlur');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // Atualiza e mostra a amostra dentro da caixa de upload
                    imgPreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                    uploadContent.style.display = 'none';

                    // Atualiza a imagem de fundo com efeito blur
                    bgBlur.style.backgroundImage = `url('${e.target.result}')`;
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>