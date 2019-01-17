
# WHMCS Provisioning Module
WHMCS Provisioning module to enable the following actions in WHMCS

- Create hosting product (WordPress or Linux)
- Create hosting packages
- Automate customer orders so hosting packages are created
- Suspend packages
- Unsuspend packages
- Single sign on to package control panels for admin and customers

## Installation

1. Drop the hyperhost server module folder into your modules > servers 
2. In WHMCS admin go to Setup > Products/Services > Servers
3. Click 'Add New Server'
4. For the name put Hyper Host
5. For the nameservers put ns1.hyper.host, ns2.hyper.host, ns3.hyper.host ns4.hyper.host
6. For the type, select Hyper Host
7. For the password, enter your API key generated at https://hyper.host/settings > Security/API Access
8. Save changes!

## All other fields can be left blank 

## Adding your products

1. Go to Setup > Products/Services > Products/Services
2. Add a new group called 'Cloud Hosting' (or whatever you want)
3. Add a new product
4. For Product Type slect Hosting Account
5. For Product Group use the one you created
6. For name call it WordPress or Linux
7. Under Module Settings, Select Hyper Host as the Module Name
8. Select the platform (WordPress or Linux)
9. Set the products auto provisioning option to "Automatically setup the product when you manually accept a pending order"
10. Save changes!

## Setting up your customers

At the moment the module does not automate customer creation so you just need to set the automated provisioning to "Automatically setup the product when you manually accept a pending order" as described above, then create the customer at https://hyper.host and add there hyper_host_id as a custom field (under Setup > Custom Client Fields) you need to call the custom field hyper_host_id
