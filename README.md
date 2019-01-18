
# WHMCS Provisioning Module
WHMCS Provisioning module to enable the following actions in WHMCS

- Automatically create Customers during order process
- Create hosting product (WordPress or Linux)
- Create hosting packages
- Automate customer orders so hosting packages are created
- Suspend packages
- Unsuspend packages
- Single sign on to package control panels for admin and customers

### Installation

01. Drop the hyperhost server module folder into your modules > servers 
02. In WHMCS admin go to Setup > Products/Services > Servers
03. Click 'Add New Server'
04. For the name put Hyper Host
05. For the nameservers put ns1.hyper.host, ns2.hyper.host, ns3.hyper.host ns4.hyper.host
06. For the type, select Hyper Host
07. For the password, enter your API key generated at https://hyper.host/settings > Security/API Access
08. Save changes!

##### Note: All other fields can be left blank

### Adding your products

01. Go to Setup > Products/Services > Products/Services
02. Add a new group called 'Cloud Hosting' (or whatever you want)
03. Add a new product
04. For Product Type slect Hosting Account
05. For Product Group use the one you created
06. For name call it WordPress or Linux
07. Under Module Settings, Select Hyper Host as the Module Name
08. Select the platform (WordPress or Linux)
09. Set the products auto provisioning option to whatever you prefer
10. Save changes!

### Setting up your customers

Before taking orders you will need to create a custom client field called hyper_host_id. You can do this under Setup > Custom Client Fields. Make sure you name the field hyper_host_id

### Setting up your hosting plans

At the moment the module only supports a single plan, and the name of the plan needs to be whmcs (lowercase) please add this plan before trying to take orders as it enables the auto creation of customers.