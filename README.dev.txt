Some tips


== JS ==
if you use JS code in your views, do not use the comment format
	// comment till EOL
The compressor/minifier that removes the spaces before sending the output will 
break the code and force the rest of the code to be sent as comment.
