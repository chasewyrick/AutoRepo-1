<?php

// $filename: Debian Package Path and Filename 
function getControlFile($filename)
{
	// Open file
	if (($ar = @fopen($filename, "rb")) === false)
		return false;
	$ar_stat = fstat($ar);

	// Check for the magic ASCII string
	if (($magic = fread($ar, 8)) === false)
		return false;
	$magic = unpack("a8", $magic);
	if (strcmp("!<arch>\n", $magic[1]))
		return false;

	// Start reading file headers
	// Ar-File headers are 60 bytes in size
	while (($file_header_string = fread($ar, 60)) !== false)
	{
		$file_header = unpack("A16name/A12timestamp/A6owner/A6group/A8mode/A10size/C2magic", $file_header_string);
		// Check for the file magic number
		if ($file_header['magic1'] != 0x60 || $file_header['magic2'] != 0x0A)
			// Assume the file is damaged
			return false;
		// Check if the file name is control.tar or control.tar.gz
		// control.tar.xz is not supported by this implementation
		if (!strcasecmp($file_header['name'], "control.tar") || !strcasecmp($file_header['name'], "control.tar.gz"))
		{
			// Found the control.tar/control.tar.gz file
			// Read its contents

			$control_size = intval($file_header['size']);
			if (ftell($ar) + $control_size >= $ar_stat['size'])
				return false;
			$control_string = fread($ar, $control_size);

			if (!strcasecmp($file_header['name'], "control.tar.gz"))
				// control.tar.gz needs to be decoded
				if (($control_string = gzdecode($control_string)) === false)
					return false;

			// Extract file "./control" from tape archive
			while (strlen($control_string) >= 512)
			{
				// Read header
				$tar_header = unpack("A100name/A8mode/A8owner/A8group/A12size/A12timestamp/A8checksum/A1type/A355padding", $control_string);
				// Check checksum
				$control_string = substr_replace($control_string, "\x20\x20\x20\x20\x20\x20\x20\x20", 148, 8);
				$calculated_sum = 0;
				for ($i = 0; $i < 512; $i++)
				{
					$calculated_sum += ord($control_string[$i]);
				}
				if (intval($tar_header['checksum'], 8) != $calculated_sum)
					// Assume the file is damaged
					return false;
				// Check if file is a normal file
				if ($tar_header['type'] == 0x00 || intval($tar_header['type']) == 0)
				{
					// Check if filename equals "./control"
					if (!strcmp($tar_header['name'], "./control"))
					{
						// Read the control file
						$size = intval($tar_header['size'], 8);
						$reqfl = 512 + $size;
						if ($reqfl >= strlen($control_string))
							return false;
						return substr($control_string, 512, $size);
					}
				}

				// Skip until next file
				$bytes = 512 + intval($tar_header['size'], 8);
				$padding = $bytes % 512;
				$padding = !$padding ? $padding : 512 - $padding;
				$bytes += $padding;

				if ($bytes >= strlen($control_string))
					return false;
				$control_string = substr($control_string, $bytes);
			}
		}

		// Seek to the next file header
		$file_size = intval($file_header['size']);
		if ($file_size & 0x1)
			$file_size++;
		if (ftell($ar) + $file_size >= $ar_stat['size'])
			return false;
		fseek($ar, $file_size, SEEK_CUR);
	}

	return true;
}