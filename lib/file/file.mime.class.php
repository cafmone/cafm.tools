<?php
	
	//-------------------------------------------------------
	/**
	 * returns mimetype gessed from extension
	 * @param $path string
	 * @return string
	 */
	//-------------------------------------------------------
	function detect_mime($path) {
	$m = '';
		$filetype=strrchr($path, ".");
		switch (strtolower($filetype)) {
			case ".ez":			$m="application/andrew-inset"; 			break;
			case ".hqx":		$m="application/mac-binhex40"; 			break;
			case ".cpt":		$m="application/mac-compactpro"; 		break;
			case ".doc":		$m="application/msword"; 				break;
			case ".bin":		$m="application/octet-stream"; 			break;
			case ".dms":		$m="application/octet-stream"; 			break;
			case ".lha":		$m="application/octet-stream"; 			break;
			case ".lzh":		$m="application/octet-stream"; 			break;
			case ".exe":		$m="application/octet-stream"; 			break;
			case ".class":		$m="application/octet-stream"; 			break;
			case ".so":			$m="application/octet-stream"; 			break;
			case ".dll":		$m="application/octet-stream"; 			break;
			case ".oda":		$m="application/oda"; 					break;
			case ".pdf":		$m="application/pdf"; 					break;
			case ".ai":			$m="application/postscript"; 			break;
			case ".eps":		$m="application/postscript"; 			break;
			case ".ps":			$m="application/postscript"; 			break;
			case ".smi":		$m="application/smil"; 					break;
			case ".smil":		$m="application/smil"; 					break;
			case ".xls":		$m="application/vnd.ms-excel"; 			break;
			case ".ppt":		$m="application/vnd.ms-powerpoint"; 	break;
			case ".pps":		$m="application/vnd.ms-powerpoint"; 	break;
			case ".wbxml":		$m="application/vnd.wap.wbxml"; 		break;
			case ".wmlc":		$m="application/vnd.wap.wmlc"; 			break;
			case ".wmlsc":		$m="application/vnd.wap.wmlscriptc"; 	break;
			case ".bcpio":		$m="application/x-bcpio"; 				break;
			case ".vcd":		$m="application/x-cdlink"; 				break;
			case ".pgn":		$m="application/x-chess-pgn"; 			break;
			case ".cpio":		$m="application/x-cpio"; 				break;
			case ".csh":		$m="application/x-csh"; 				break;
			case ".dcr":		$m="application/x-director"; 			break;
			case ".dir":		$m="application/x-director"; 			break;
			case ".dxr":		$m="application/x-director"; 			break;
			case ".dvi":		$m="application/x-dvi"; 				break;
			case ".spl":		$m="application/x-futuresplash"; 		break;
			case ".gtar":		$m="application/x-gtar"; 				break;
			case ".hdf":		$m="application/x-hdf"; 				break;
			case ".js": 		$m="application/x-javascript"; 			break;
			case ".skp":		$m="application/x-koan"; 				break;
			case ".skd":		$m="application/x-koan"; 				break;
			case ".skt":		$m="application/x-koan"; 				break;
			case ".skm":		$m="application/x-koan"; 				break;
			case ".latex":		$m="application/x-latex"; 				break;
			case ".nc":			$m="application/x-netcdf"; 				break;
			case ".cdf":		$m="application/x-netcdf"; 				break;
			case ".shar":		$m="application/x-shar"; 				break;
			case ".swf":		$m="application/x-shockwave-flash"; 	break;
			case ".sit":		$m="application/x-stuffit"; 			break;
			case ".sv4cpio":	$m="application/x-sv4cpio"; 			break;
			case ".sv4crc":		$m="application/x-sv4crc"; 				break;
			case ".tar":		$m="application/x-tar"; 				break;
			case ".tcl":		$m="application/x-tcl"; 				break;
			case ".tex":		$m="application/x-tex"; 				break;
			case ".texinfo":	$m="application/x-texinfo"; 			break;
			case ".texi":		$m="application/x-texinfo"; 			break;
			case ".t":			$m="application/x-troff"; 				break;
			case ".tr":			$m="application/x-troff"; 				break;
			case ".roff":		$m="application/x-troff"; 				break;
			case ".man":		$m="application/x-troff-man"; 			break;
			case ".me":			$m="application/x-troff-me"; 			break;
			case ".ms":			$m="application/x-troff-ms"; 			break;
			case ".ustar":		$m="application/x-ustar"; 				break;
			case ".src":		$m="application/x-wais-source"; 		break;
			case ".xhtml":		$m="application/xhtml+xml"; 			break;
			case ".xht":		$m="application/xhtml+xml"; 			break;
			case ".zip":		$m="application/zip"; 					break;
			case ".au":			$m="audio/basic"; 						break;
			case ".snd":		$m="audio/basic"; 						break;
			case ".mid":		$m="audio/midi"; 						break;
			case ".midi":		$m="audio/midi"; 						break;
			case ".kar":		$m="audio/midi"; 						break;
			case ".mpga":		$m="audio/mpeg"; 						break;
			case ".mp2":		$m="audio/mpeg"; 						break;
			case ".mp3":		$m="audio/mpeg"; 						break;
			case ".aif":		$m="audio/x-aiff"; 						break;
			case ".aiff":		$m="audio/x-aiff"; 						break;
			case ".aifc":		$m="audio/x-aiff"; 						break;
			case ".m3u":		$m="audio/x-mpegurl"; 					break;
			case ".ram":		$m="audio/x-pn-realaudio"; 				break;
			case ".rm":			$m="audio/x-pn-realaudio"; 				break;
			case ".rpm":		$m="audio/x-pn-realaudio-plugin"; 		break;
			case ".ra":			$m="audio/x-realaudio"; 				break;
			case ".wav":		$m="audio/x-wav"; 						break;
			case ".pdb":		$m="chemical/x-pdb"; 					break;
			case ".xyz":		$m="chemical/x-xyz"; 					break;
			case ".bmp":		$m="image/bmp"; 						break;
			case ".gif":		$m="image/gif"; 						break;
			case ".ief":		$m="image/ief"; 						break;
			case ".jpeg":		$m="image/jpeg"; 						break;
			case ".jpg":		$m="image/jpeg"; 						break;
			case ".jpe":		$m="image/jpeg"; 						break;
			case ".png":		$m="image/png"; 						break;
			case ".tiff":		$m="image/tiff"; 						break;
			case ".tif":		$m="image/tiff"; 						break;
			case ".djvu":		$m="image/vnd.djvu"; 					break;
			case ".djv":		$m="image/vnd.djvu"; 					break;
			case ".wbmp":		$m="image/vnd.wap.wbmp"; 				break;
			case ".ras":		$m="image/x-cmu-raster"; 				break;
			case ".pnm":		$m="image/x-portable-anymap"; 			break;
			case ".pbm":		$m="image/x-portable-bitmap"; 			break;
			case ".pgm":		$m="image/x-portable-graymap"; 			break;
			case ".ppm":		$m="image/x-portable-pixmap"; 			break;
			case ".rgb":		$m="image/x-rgb"; 						break;
			case ".xbm":		$m="image/x-xbitmap"; 					break;
			case ".xpm":		$m="image/x-xpixmap"; 					break;
			case ".xwd":		$m="image/x-xwindowdump"; 				break;
			case ".igs":		$m="model/iges"; 						break;
			case ".iges":		$m="model/iges"; 						break;
			case ".msh":		$m="model/mesh"; 						break;
			case ".mesh":		$m="model/mesh"; 						break;
			case ".silo":		$m="model/mesh"; 						break;
			case ".wrl":		$m="model/vrml"; 						break;
			case ".vrml":		$m="model/vrml"; 						break;
			case ".css":		$m="text/css"; 							break;
			case ".html":		$m="text/html"; 						break;
			case ".htm":		$m="text/html"; 						break;
			case ".asc":		$m="text/plain"; 						break;
			case ".csv":		$m="text/csv";							break;
			case ".php":		$m="text/plain"; 						break;
			case ".ini":		$m="text/plain"; 						break;
			case ".txt":		$m="text/plain"; 						break;
			case ".rtx":		$m="text/richtext"; 					break;
			case ".rtf":		$m="text/rtf"; 							break;
			case ".sgml":		$m="text/sgml"; 						break;
			case ".sgm":		$m="text/sgml"; 						break;
			case ".sh":			$m="text/plain"; 						break;
			case ".tsv":		$m="text/tab-separated-values"; 		break;
			case ".wml":		$m="text/vnd.wap.wml"; 					break;
			case ".wmls":		$m="text/vnd.wap.wmlscript"; 			break;
			case ".etx":		$m="text/x-setext"; 					break;
			case ".xml":		$m="text/xml"; 							break;
			case ".xsl":		$m="text/xml"; 							break;
			case ".mpeg":		$m="video/mpeg"; 						break;
			case ".mpg":		$m="video/mpeg"; 						break;
			case ".mpe":		$m="video/mpeg"; 						break;
			case ".qt":			$m="video/quicktime"; 					break;
			case ".mov":		$m="video/quicktime"; 					break;
			case ".mxu":		$m="video/vnd.mpegurl"; 				break;
			case ".avi":		$m="video/x-msvideo"; 					break;
			case ".movie":		$m="video/x-sgi-movie"; 				break;
			case ".asf":		$m="video/x-ms-asf"; 					break;
			case ".asx":		$m="video/x-ms-asf"; 					break;
			case ".wm":			$m="video/x-ms-wm"; 					break;
			case ".wmv":		$m="video/x-ms-wmv"; 					break;
			case ".wvx":		$m="video/x-ms-wvx"; 					break;
			case ".ice":		$m="x-conference/x-cooltalk"; 			break;
			case ".heif":		$m="image/heif";						break;
			case ".heic":		$m="image/heif";						break;
		}
	return $m;
	}

?>
