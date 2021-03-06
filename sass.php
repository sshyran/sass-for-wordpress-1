<?php
/*
MIT-LICENSE

Copyright (c) 2009 80beans B.V.
 
Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:
 
The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.
 
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * @package Sass
 * @author 80beans B.V.
 * @version 1.0
 */
/*
Plugin Name: Sass for Wordpress
Plugin URI: http://www.80beans.com/open-source/sass-for-wordpress-plugin/
Description: "Sass for Wordpress" enables you to use Sass (Syntactically Awesome StyleSheets, http://sass-lang.com/) in your Wordpress project.
Author: 80beans B.V.
Version: 1.0
Author URI: http://www.80beans.com/
*/

function sass($filename)
{
	// Correct some user input (using '.css', '.sass' or '.scss').
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if ($ext == 'css' || $ext == 'sass' || $ext == 'scss')
	{
		$filename = substr($filename, 0, -(1 + strlen($ext)));
	}

	$filepath = $filename;
	
	// Check whether user already specified the template path
	if (substr($filename, 0, strlen(TEMPLATEPATH.DIRECTORY_SEPARATOR)) == TEMPLATEPATH.DIRECTORY_SEPARATOR)
	{
		$filename = substr($filename, strlen(TEMPLATEPATH.DIRECTORY_SEPARATOR));
	}
	else
	{
		$filepath = TEMPLATEPATH . DIRECTORY_SEPARATOR . $filepath;
	}
	
	// Store the filesystem paths for the Sass and CSS filenames in variables.
	$css_filename = $filepath . '.css';
	$sass_filename = $filepath .'.sass';
	$scss_filename = $filepath .'.scss';

	// If the Sass doesn't exist, throw an error.
	if (!file_exists($sass_filename) && !file_exists($scss_filename))
	{
		sass_error($css_filename, 'File ' . $filepath . '.(sass|scss) does not exist.');
	}
	// Now we're sure there's a Sass file to transform, let's do it!
	else
	{
		$input_filename = (file_exists($sass_filename) ? $sass_filename : $scss_filename);
		
		if (!file_exists($css_filename) || filemtime($css_filename) < filemtime($input_filename))
		{
		
			@unlink($css_filename);
			exec('sass ' . escapeshellarg($input_filename) . ' ' . escapeshellarg($css_filename) . ' 2>&1', $output, $return);
		
			if ($return != 0)
			{
				if ($return == 127)
					sass_error($css_filename, 'Sass executable not found. Verify that it is installed and on the PATH.');
				else
					sass_error($css_filename, "Sass command did not exit cleanly. Command output follows\n\n" . join("\n",$output));
			
				// Set the modified time to BEFORE the sass file mtime, so that the user doesn't have to manually delete the css to cause a refresh.
				@touch($css_filename, filemtime($input_filename) -1);
			}
		}
	}
	
	return get_bloginfo('template_directory') . '/' . $filename . '.css';
}

// This function throws an error by using the CSS ':before' pseudo-element.
function sass_error($css_filename, $error)
{
	$trans = array(
		'"' => '\"',
		"\n"=> '\A',
	);
	
	@unlink($css_filename);
	file_put_contents($css_filename, 'body:before { white-space: pre; font-family: monospace; content: "Sass for Wordpress error: ' . strtr($error, $trans) . '"; }');
}
