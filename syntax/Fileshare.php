<?php
/**
 * DokuWiki Plugin fileshare (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Soft2C.de <info@soft2c.de>
 */

if (! defined ( 'NL' ))
	define ( 'NL', "\n" );
if (! defined ( 'DOKU_INC' ))
	define ( 'DOKU_INC', dirname ( __FILE__ ) . '/../../' );
if (! defined ( 'DOKU_PLUGIN' ))
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );
require_once (DOKU_PLUGIN . 'syntax.php');
require_once (DOKU_INC . 'inc/media.php');
require_once (DOKU_INC . 'inc/auth.php');

require_once (DOKU_INC . 'inc/infoutils.php');

class syntax_plugin_fileshare_Fileshare extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'container';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'normal';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 280;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
    	$this->Lexer->addSpecialPattern ( '\{\{FileSharing\}\}', $mode, 'plugin_fileshare_Fileshare' );
    	$this->Lexer->addSpecialPattern ( '\{\{FileSharing>.+\}\}', $mode, 'plugin_fileshare_Fileshare' );
    }

    /**
     * Handle matches of the fileshare syntax
     *
     * @param string          $match   The match of the syntax
     * @param int             $state   The state of the handler
     * @param int             $pos     The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler){
    	$pos = strpos($match, "{{FileSharing>");
    	if($pos !== FALSE){
    		$data = explode("|",preg_replace("/{{FileSharing>(.*?)}}/","\\1",$match));
    		return $data;
    	}
    	
    	$data = array ();
    	return $data;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
    	//if ($_FILES ['upload'] ['tmp_name']) {    	
    	if(isset($_FILES['upload']) ){
    		$dir = $_POST ['ns'];
    		$tmp_name = $_FILES ['upload'] ['tmp_name'];
    		// basename() kann Directory-Traversal-Angriffe verhindern;
    		// weitere Validierung/Bereinigung des Dateinamens kann angebracht sein
    		$name = basename ( $_FILES ['upload'] ['name'] );
    			
    		if ( move_uploaded_file ( $tmp_name, $dir . '/' . $name )=== FALSE) {
    			$lastError = error_get_last();
    			$err = $lastError ? "Error: ".$lastError["message"]." on line ".$lastError["line"] : "";
    			$renderer->doc .= 'Fehler beim Upload! '.$err;
    		}
    	}
        if($mode != 'xhtml') return false;
        
        $renderer->nocache();
        
        if(empty($data)){
        	$givenDir='';
        }
        else{
        	$givenDir= current($data);
        }
        
        $this->renderForm ( $givenDir, $renderer  );
        
        return true;
    }
    
    private function renderForm($dir, $renderer) {
    	global $ID;
    	$archivRootDir = $this->getConf ( 'archivRootDir' );
    	
    	if(!isset($archivRootDir) || trim($archivRootDir)===''){
    		$renderer->doc .= 'Please set the root dir to archive files in the admin section!';
    	}
    	else
    	{
    		$fileDir = $archivRootDir;
    		
    		if(isset($dir) && trim($dir)!==''){
    			$fileDir =$fileDir. '/' . $dir;
    		}
    			
    		$this->createDir ( $fileDir, $renderer );
    			

    					
    		$renderer->doc .= '<div style="padding:4px 8px 4px 8px;">';
    			
    		$renderer->doc .= '<h2>' .  $fileDir . '</h2>';
    			

    		$delete = false;
    		if (isset ( $_GET ['action'] )) {
    			$action = $_GET ['action'];
    			if (strcmp ( $action, 'DELETE' ) == 0) {
    				$delete = true;
    			}
    		}
    			
//     		$renderer->doc .= '<form action="' . wl ( $ID ) . '" method="GET" name="fileshare" id="fileshare">';
//     		$renderer->doc .= <<<EOT
// <input type="hidden" name="id" value="$_REQUEST[id]" />
// EOT;
//     		$renderer->doc .= '</form>';
			if($this->isAuthorized('role_upload')){
    			$renderer->doc .= $this->upload_plugin_uploadform ( $fileDir );
			}
    		$renderer->doc .= '</div>';
    		$renderer->doc .= $this->readFileList ( $fileDir, $delete);
    	}
    }
    
    private function createDir($dir, $renderer) {
    	if (! file_exists ( $dir )) {
    		if (! mkdir ( $dir, 0755, true )) {
    			$renderer->doc .= 'Erstellung der Verzeichnisse: ' . $dir . ' schlug fehl...';
    		}
    	}
    }
    
    private function readFileList($dir, $delete) {
    	$refreshbutton = $this->getLang ( 'refreshbutton' );
    	$loeschenbutton = $this->getLang ( 'loeschenbutton' );
    	$html ='';
    	$html .= '<div style="padding:4px 8px 4px 8px;">';
    	$html .= '<form action="' . $_SERVER ['PHP_SELF'] . '" method="GET" name="filesharelist" id="filesharelist">';
    	$html .= '<input type="hidden" name="id_" value="' . $_REQUEST['id']. '" />';
    	if($this->isAuthorized('role_delete')){
	    	$html .= '<button style="float:left" name="action" value="DELETE" type="submit"> ' . $loeschenbutton . ' </button>';
	    	$html .= '<button style="float:left" name="action" value="REFRESH" type="submit"> ' . $refreshbutton . ' </button>';
    	}  	
    	$cdir = scandir ( $dir );
    	foreach ( $cdir as $key => $value ) {
    		if (! in_array ( $value, array (
    				".",
    				".."
    		) )) {
    			$fileHash = 'f'.hash('md5', $value, false);
    			if( $delete == true && isset (  $_GET[$fileHash] )&& strcmp ( $_GET[$fileHash], 'on' ) == 0){ 
    				if(unlink($dir.'/'.$value)== true){
    				$html .= '<br>'.$this->getLang ( 'file_deleted' ) . $value;
    				}
    				else{
    					$html .= '<br>'.$this->getLang ( 'file_not_deleted' );
    					if($this->isAuthorized('role_download'))
    						$html .= '<a href="filesharedownload.php?file=' . $value . '&something=' . hsc ( $dir ) . '">' . $value . '</a>';
    					else
    						$html .=$value;
    				}
    			}else{
    				if($this->isAuthorized('role_delete'))
	    				$html .= '<br><input type="checkbox" id="' . $fileHash . '" name="' . $fileHash . '" >  : ';
    				else
    					$html .= '<br>';
	    			if($this->isAuthorized('role_download'))
    					$html .= '<a href="filesharedownload.php?file=' . $value . '&something=' . hsc ( $dir ) . '">' . $value . '</a>';
    				else
    					$html .= $value;
    			}
    		}
    	}
    	$html .= '</form>';
    	$html .= '</div>';
    	
    	return $html;
    }
    
    function upload_plugin_uploadform($ns) {
    	global $ID;
    	global $lang;
    	$html = '';
    
    	$params = array ();
    	$params ['id'] = 'plugin_fileshare_Fileshare';
    	$params ['action'] = wl ( $ID );
    	$params ['method'] = 'post';
    	$params ['enctype'] = 'multipart/form-data';
    	$params ['class'] = 'plugin_fileshare_Fileshare';
    
    	// Modification of the default dw HTML upload form
    	$form = new Doku_Form ( $params );
    	$form->addElement ( formSecurityToken () );
    	// 		$form->addHidden ( 'page', hsc ( $ID ) );
    	$form->addHidden ( 'ns', hsc ( $ns ) );
    	$form->addElement ( form_makeFileField ( 'upload', '', 'upload__file' ) );
    	$form->addElement ( form_makeButton ( 'submit', '', $lang ['btn_upload'] ) );
    	$form->endFieldset ();
    
    	$html .= '<div class="plugin_fileshare_Fileshare"><p>' . NL;
    	$html .= $form->getForm ();
    	$html .= '</p></div>' . NL;
    	return $html;
    }
    
    private function isAuthorized($settingRole) {
    	$allowedUserGroups = $this->getConf ( $settingRole);
    	
    	$allowedUserGroups = utf8_strtolower ( $allowedUserGroups );
    	$members = explode ( ',', $allowedUserGroups );
    	$members = array_map ( 'trim', $members );
    	$members = array_unique ( $members );
    	$members = array_filter ( $members );
    	
    	// compare cleaned values
    	foreach ( $members as $member ) {
    		if ($member == 'all')
    			return true;
    	}
    	
    	global $INPUT;
    	$remoteUser = $INPUT->server->str ( 'REMOTE_USER' );
    
    	if (! $remoteUser) {
    		return false;
    	}
    
    	global $USERINFO;
    	$groups = $USERINFO ['grps'];
    	
    	foreach ( $members as $member ) {
	    	if (in_array ( $member, $groups ))
	    		return true;
	    	else {
	    		if ($member == $remoteUser)
	    			return true;
	    	}
    	}

    	return false;
    }
}

// vim:ts=4:sw=4:et:
