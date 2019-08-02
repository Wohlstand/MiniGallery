=================================================================
=================================================================
            MiniGallery v1.2 by Wohlstand
=================================================================
https://github.com/Wohlstand/MiniGallery
=================================================================

Gallery is works based on alone (this) file which adds linked php-files into
subfolders (which includes THIS file)

This gallery uses the FancyBox java-scripts to allow interactive preview of the images.

To define order and it's direction use a _sortby.txt file
To show another title instead filename of specific file, use the "_desc.txt" file.

=================================================================

The MIT License (MIT)

Copyright (c) 2016 Vitaly Novichkov "Wohlstand" <admin@wohlnet.ru>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
=================================================================
=================================================================
Files which are will be shown in the gallery:
=================================================================
JPG, JPEG, PNG, GIF, TXT, ZIP, SWF
=================================================================


=================================================================
=================================================================
Before start using, you should check preferences in this file:
                      index.options.php
=================================================================

=================================================================

**********************************************************************
Additional config files (per every folder):
**********************************************************************
=================================================================
                         _sortby.txt
=================================================================

If this file is not presented, default order is by date desc.

Syntax of _sortby.txt file:
In the file are defined two lines, a two required parameters:
__________________________________________
sortby
sortvector
__________________________________________
Where sortby - Field to order
There are supported name or date

Where sortvector - direction of the order
Supported directions: asc or desc

Example:
____________
name
asc
____________
Sort by name asc.

=================================================================
=================================================================
                          _desc.txt
=================================================================
_desc.txt file can be used to redefine filename with custom title.
Syntax:
File contains a list of files/folders and it's replacement text
___________________________________________
file.jpg|My test file
lena.png|Lena - my pet fox
...
___________________________________________
