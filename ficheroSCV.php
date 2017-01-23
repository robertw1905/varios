<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <title>Subir archivos al servidor</title>
    <style type="text/css" media="screen">
        body{font-size:1.2em;}
    </style>
    <script type="text/javascript">
        function validar_post()
        {
            var input = document.getElementById('uploadedfile');
            var file = input.files[0];
            if (typeof file === 'undefined')
            {
                return true;
            }else{
                if ( 
                    (file.size > <?=Upload_file::return_bytes(ini_get('post_max_size'))?>) ||
                    (file.size > <?=Upload_file::return_bytes(ini_get('upload_max_filesize'))?>)
                    )
                {
                    alert("Tamaño del archivo supera la configuración de subida en el servidor")
                    return false;
                }
            }
            return true
        }
    </script>
</head>
<body>
    <form enctype="multipart/form-data" action="" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="8388608" />
        <input name="uploadedfile" id="uploadedfile" type="file">
        <input type="submit" value="Subir archivo" onclick="return validar_post();">
    </form> 
</body>
</html> 

<?php
//
//  Inicio de Php
//
if (!empty($_FILES))
{
    $upfile = new Upload_file($_FILES, "uploads/", array("csv"));

    if ($upfile->inicio())
    {
        $upfile->readFile();
        //$upfile->upload();
    }
        echo $upfile->msg;
    
}

/**
 * Clase para subir archivos con 
 * 
 */

class Upload_file
{
    protected $file;
    protected $target_path;
    public $msg;
    protected $format;

    function __construct($file, $target_path = "", $format = array())
    {
        $this->file = $file;
        $this->target_path = $target_path;
        $this->msg = "";
        $this->format = $format;
    }

    public function inicio()
    {
        return $this->validar();
    }

    protected function validar()
    {
        $error = $this->file["uploadedfile"]["error"];

        switch ($error) {
            case UPLOAD_ERR_OK:
                    $uploadedfileload="true";
                    $uploadedfile_size=$this->file['uploadedfile']['size'];
                    if ($uploadedfile_size > $this->return_bytes(ini_get('post_max_size')))
                    {
                        $this->msg = $this->msg."El archivo es mayor que ".ini_get('post_max_size').", debes reducirlo antes de subirlo<BR>";
                        $uploadedfileload="false";
                        return false;
                    }

                    $partes_ruta = pathinfo(basename($this->file["uploadedfile"]["name"]));

                    if (!in_array($partes_ruta['extension'], $this->format, true))
                    {
                        $this->msg = $this->msg." Tu archivo tiene que ser CSV. Otros archivos no son permitidos<BR>";
                        $uploadedfileload="false";
                        unset($this->file);
                        return false;
                    }
                    if($uploadedfileload=="true")
                    {
                        return true;
                    }
                break;
            case UPLOAD_ERR_INI_SIZE:
                    $this->msg = $this->msg."El fichero subido excede la directiva upload_max_filesize de php.ini.";
                    return false;
                break;
            case UPLOAD_ERR_FORM_SIZE:
                    $this->msg = $this->msg."El fichero subido excede la directiva MAX_FILE_SIZE especificada en el formulario HTML";
                    return false;
                break;
            case UPLOAD_ERR_PARTIAL:
                    $this->msg = $this->msg."El fichero fue sólo parcialmente subido.";
                    return false;
                break;
            case UPLOAD_ERR_NO_FILE:
                    $this->msg = $this->msg."No se subió ningún fichero.";
                    return false;
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                    $this->msg = $this->msg."Falta la carpeta temporal. Introducido en PHP 5.0.3.";
                    return false;
                break;
            case UPLOAD_ERR_CANT_WRITE:
                    $this->msg = $this->msg."No se pudo escribir el fichero en el disco. Introducido en PHP 5.1.0.";
                    return false;
                break;
            case UPLOAD_ERR_EXTENSION:
                    $this->msg = $this->msg."Una extensión de PHP detuvo la subida de ficheros.";
                    return false;
                break;
            default:
                    
                break;
        }

        if ($error == UPLOAD_ERR_OK)
        {
            
        }else{
            $this->msg = $this->msg."Seleccione un archivo";
            return false;
        }
    }

    public function upload()
    {
        $tmp_name = $this->file["uploadedfile"]["tmp_name"];
        $name = basename($this->file["uploadedfile"]["name"]);
        
        $partes_ruta = pathinfo($name);
        $name = $partes_ruta['filename'].time().".".$partes_ruta['extension'];

        $this->target_path = $this->target_path . $name; 

        if(move_uploaded_file($tmp_name, $this->target_path)) 
        {
            $this->msg = "<span style='color:green;'>El archivo ". $name . " ha sido subido satisfactoriamente</span><br>";
            unset($this->file);
        }else{
            $this->msg = "No se pudo subir el archivo";
        }
    }

    public function readFile()
    {
        $registros = array();
        if (($fichero = fopen($this->file['uploadedfile']['name'], "r")) !== FALSE)
        {
            // Lee los nombres de los campos
            $nombres_campos = fgetcsv($fichero, filesize(basename($this->file["uploadedfile"]["name"])), ",", "\"", "\"");
            $num_campos = count($nombres_campos);
            // Lee los registros
            while (($datos = fgetcsv($fichero, filesize(basename($this->file["uploadedfile"]["name"])), ",", "\"", "\"")) !== FALSE)
            {
                // Crea un array asociativo con los nombres y valores de los campos
                for ($icampo = 0; $icampo < $num_campos; $icampo++)
                {
                    $registro[$nombres_campos[$icampo]] = $datos[$icampo];
                }
                // Añade el registro leido al array de registros
                $registros[] = $registro;
            }
            fclose($fichero);

            echo "Leidos " . count($registros) . " registros\n<br>";

            foreach ($nombres_campos as $key => $value)
            {
                for ($i = 0; $i < count($registros); $i++)
                {
                    echo "Rut: " . $registros[$i][$value] . "\n<br>";
                }
            }
        }
    }

    public function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // El modificador 'G' está disponble desde PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
}
?>