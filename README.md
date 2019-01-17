# whmcs-provisioning-modue
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

## All other fields can be left blank 
