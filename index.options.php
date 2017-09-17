<?php
//Relative HTTP-path to folder with gallery (in the browser)
$Photosfolder = "/gallery/";

//Absolute file path to folder with gallery (on the server)
$PhotosPath = dirname(__FILE__)."/";

//Default title of the page
$PageTitle = "My Photos";

//IP-address of administrator
/*
Admins are allowed to do on the pages of the gallery:
- Rename files
- Delete files
- Change order sequence and direction from web (instead of _sortby.txt usage)
*/
$AdminIp = "Put-Your-IP-Address-Here";
//Examples:
//172.16.45.123 //Absolute address
//172.20.       //Subnet 172.20.*.*
//172.20.0.     //Subnet 172.20.0.*
//20.0          //Any IP-Address that contains "20.0"
$AdminIPs = ["Put-Your-IP-Address-Here", "And-Here"];

//Note: You cloud replace IP-address based check with more secure thing than this. (for example)

//File system charset (UTF-8 is fine on any UNIX-like systems, but carefully set up charset on the Windows server!)
$FSCharset = "utf-8";

//Relative path to the gallery page background image
$ImgBackground = "/gallery/_img/docs.jpg";


