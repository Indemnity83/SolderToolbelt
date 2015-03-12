<?php namespace Indemnity83\SolderToolbelt;

setlocale(LC_ALL, 'en_US.UTF8');

class ModLib {

  public $modFile;
  public $slug;
  public $name;
  public $version;
  public $mcversion;

  public function __construct($mod){
    $this->modFile = realpath($mod);

    // Make sure file exists
    if( !file_exists($this->modFile) ) {
      throw new \Exception('File not found');
    }

    // Attempt to open the zip and extract the mod info file
    $zip = new \ZipArchive;
    try {
      $zip->open($this->modFile);
      $zip->extractTo('./', array('mcmod.info'));
      $zip->close();
      $mcmod = file_get_contents('./mcmod.info');
      $mcmod = json_decode($mcmod);
      unlink('./mcmod.info');
    } catch (\Exception $e) {
      throw $e;
    }

    // Record mod info
    if( isset($mcmod->modinfoversion) && $mcmod->modinfoversion == '2') {
      $this->name = $mcmod->modlist[0]->name;
      $this->slug = $this->slug($this->name);
      $this->version = $mcmod->modlist[0]->version;
      $this->mcversion = $mcmod->modlist[0]->mcversion;
    } else {
      $this->name = $mcmod[0]->name;
      $this->slug = $this->slug($this->name);
      $this->version = $mcmod[0]->version;
      $this->mcversion = $mcmod[0]->mcversion;
    }
  }

  public function package($dest = NULL, $configs = array(), $suppressFolder = FALSE, $expandFolder = 'mods') {
    // Check that required parameters exist
    if( $this->slug == '' || $this->mcversion == '' || $this->version == '') {
      throw new \Exception('Missing mod info');
    }

    if ($dest == NULL) {
      $dest = dirname($this->modFile);
    } else {
      $dest = realpath($dest);
    }

    if ($suppressFolder) {
      $fileName = $dest . DIRECTORY_SEPARATOR .
        $this->slug . '-' .
        $this->mcversion . '-' .
        $this->version . '.zip';
    } else {
      if(!is_dir($dest . DIRECTORY_SEPARATOR . $this->slug)){
        mkdir($dest . DIRECTORY_SEPARATOR . $this->slug);
      }
      $fileName = $dest . DIRECTORY_SEPARATOR .
        $this->slug . DIRECTORY_SEPARATOR .
        $this->slug . '-' .
        $this->mcversion . '-' .
        $this->version . '.zip';
    }

    // Attempt to add files to the package
    $zip = new \ZipArchive;
    try {
      $zip->open($fileName, \ZipArchive::OVERWRITE);

      // Add the mod itself
      $zip->addFile($this->modFile, $expandFolder . DIRECTORY_SEPARATOR . basename($this->modFile));

      // Add config files/directories
      foreach( $configs as $path ) {
        $path = realpath($path);
        if( is_dir($path)) {
          throw new \Exception('Directories not yet supported');
        } else {
          $zip->addFile($path, 'configs' . DIRECTORY_SEPARATOR . basename($path));
        }
      }
    } catch (Exception $e) {
      throw $e;
    } finally {
      $zip->close();
    }
  }


  function slug($str, $replace=array(), $delimiter='-') {
  	if( !empty($replace) ) {
  		$str = str_replace((array)$replace, ' ', $str);
  	}

  	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
  	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
  	$clean = strtolower(trim($clean, '-'));
  	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

  	return $clean;
  }
}
