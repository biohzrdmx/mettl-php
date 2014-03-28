# mettl-php #

##Introduction##

A simple PHP interface for the Mettl ReST API

Conforming to Mettl API v1.9

###Credits###

**Lead coder:** biohzrdmx [&lt;github.com/biohzrdmx&gt;](http://github.com/biohzrdmx)

###License###

The MIT License (MIT)

Copyright (c) 2014 biohzrdmx

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## Basic usage ##

Just include/require the `mettl.class.php` file and create a new `Mettl` object passing your API credentials as an argument:

	$credentials = array(
		'public_key' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
		'private_key' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
	);

	$mettl = Mettl::newInstance($credentials);

Once you've instantiated the object, you may use any of its methods to interact with the Mettl API:

	$assessments = $mettl->getAssessments();

All of the methods will return an object containing the API response or `false` on error.

## Available methods ##

Please refer to the class itself, every method has its own documentation block.

## Troubleshooting ##

This class requires the cURL library.

If you find an error feel free to ticket an issue.

If you want to know more about the Mettl API you may download the latest version from here: [http://support.mettl.com/support/solutions/articles/119246-api-integration-with](http://support.mettl.com/support/solutions/articles/119246-api-integration-with)

## Improving ##

So you think you can do a better job? That's great! Clone the repo, make your changes and send me a pull-request.