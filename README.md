# Crunchbutton
### Push buttons, get paid.


##### JS
If you use JS code in your views, do **not** use the comment format
```
// comment till EOL
```
The compressor/minifier that removes the spaces before sending the output will break the code and force the rest of the code to be sent as comment. Instead use flower boxes.


##### DB import
When importing the dbs triggers, keep in mind the Definer field. you may want to just create a user called 
```
"devin"@"%"
```
