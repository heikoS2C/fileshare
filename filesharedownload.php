<?php

if (isset($_GET['file'])){
	$filename =$_GET['file'];
	$archivRootDir = $_GET[ 'something'];
		
	$downloadFile = $archivRootDir . '/' . $filename;
	
	header ( "Content-Type: ". get_mime_type($filename) );
	header ( "Content-Disposition: attachment; filename=" . $filename );
	header ( "Content-Length: " . filesize ( $downloadFile ) );
	readfile ( $downloadFile );
	return;
}


function get_mime_type($filename) {
	$idx = explode( '.', $filename );
	$count_explode = count($idx);
	$idx = strtolower($idx[$count_explode-1]);

	$mimet = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',


			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

			// START MS Office 2007 Docs
			'docx'
			=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'docm'
			=> 'application/vnd.ms-word.document.macroEnabled.12',
			'dotx'
			=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'dotm'
			=> 'application/vnd.ms-word.template.macroEnabled.12',
			'xlsx'
			=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xlsm'
			=> 'application/vnd.ms-excel.sheet.macroEnabled.12',
			'xltx'
			=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'xltm'
			=> 'application/vnd.ms-excel.template.macroEnabled.12',
			'xlsb'
			=> 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'xlam'
			=> 'application/vnd.ms-excel.addin.macroEnabled.12',
			'pptx'
			=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'pptm'
			=> 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'ppsx'
			=> 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'ppsm'
			=> 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'potx'
			=> 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'potm'
			=> 'application/vnd.ms-powerpoint.template.macroEnabled.12',
			'ppam'
			=> 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
			'sldx'
			=> 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'sldm'
			=> 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
			'one'
			=> 'application/msonenote',
			'onetoc2'
			=> 'application/msonenote',
			'onetmp'
			=> 'application/msonenote',
			'onepkg'
			=> 'application/msonenote',
			'thmx'
			=> 'application/vnd.ms-officetheme'
	);

	if (isset( $mimet[$idx] )) {
		return $mimet[$idx];
	} else {
		return 'application/octet-stream';
	}

}