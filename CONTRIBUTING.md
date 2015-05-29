### Guide to Reporting Issues

To report development issues, please make sure you provide as much information as possible to help identify the problem.

**If an issue is blocking orders being fullfilled, ie, order notifications not going out, drivers cant accept orders, users cant place orders, add the label BLOCKING**

#### For user/Crunchbutton related issues

1. Browser
2. Operating system or Mobile device
3. Specific page and info enterd on that page
4. User name, phone, and order
5. A detailed account of what happened in order

#### For admin/Cockpit related issues

1. Browser
2. Operating System or mobile device
3. Specific page and info enterd on that page
4. **Console output** - for reporting javascript errors:
  1. Right click, and select Inspect Element (In Firefox or Chrome):
     <br>![image](https://cloud.githubusercontent.com/assets/10369508/6260200/01191b74-b790-11e4-89c3-f430962856c1.png)
  2. Select "console" all the way to the right: (in Firefox it's second from the left)
     ![image](https://cloud.githubusercontent.com/assets/10369508/6260238/9c213160-b790-11e4-93b8-0c2df98ef386.png)
  3. Take a screenshot of it if there's anything written there.
    - To take a screenshot for Mac of just one area: shift + command + control + 4. This will copy it to your clipboard. Hit command + v into the ticket/issue and the picture will show up. 
    - To take a screenshot for Windows: Use the snipping tool and drag the picture into the issue/ticket. OR alt + PrtScnSysRq.     - Then drag that into the ticket/issue and the picture will show up.
      
5. **XHR response** - for reporting loading/saving errors - only if the spinner doesn't stop or if something doesn't load or save (in order to get XHR, you will need to refresh the page, follow these steps, then reproduce the error):
  1. Right click, and select Inspect Element (In Firefox or Chrome):
     <br>![image](https://cloud.githubusercontent.com/assets/10369508/6260200/01191b74-b790-11e4-89c3-f430962856c1.png)
  2. Select "network" second from the left: (in Firefox it's all the way to the right)
     ![image](https://cloud.githubusercontent.com/assets/10369508/6260328/05d47404-b792-11e4-9799-4ee9a0f3e542.png)
  4. Click on "XHR"
  5. Select the last item in the list. This is *not* heartbeat or count. You want to click on the request you are trying to debug.
  6. Click on "Response"
     ![image](https://cloud.githubusercontent.com/assets/27974/7008609/47bb07e4-dc49-11e4-9281-2610a2885ce5.png)
  7. Then take a screenshot of what's there. 

*If a developer has a question, please make sure to respond ASAP, or it will just get closed and not addressed properly.*

**It is always better to provide too much information than too little.**
