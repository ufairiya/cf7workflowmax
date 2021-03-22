# cf7workflowmax
Contact Form 7 extension for Workflowmax API integration (WORDPRESS - WORKFLOWMAX API INTEGRATION)

WordPress plugin as an extension/addon of CF7.
 
 Main functionality:
   1. Saves the Contact Form 7 submissions to the WordPress
      database.
   2. Call the WorkFlowMax API (authentication using OAuth2.0 and proceed to call the API)
   3. Create a new user in wfm
   4. Create a job in wfm



Plugin will connect to Workflowmax using their existing OAuth2 system (credentials have been 
generated and will be supplied)

Plugin will generate a client in the workflowmax system with the values provided by the 
contact form, UNLESS the client already exists in the system

Plugin will retrieve the clients unique ID that is generated from workflowmax

Plugin then generates a lead in the workflowmax system using the clients unique ID 
and the values provided by the contact form


Client Payload:
	Client Name
	Client Email
	Client Contact Number
	Site Address

Lead Payload
	Name (Client Name + Site Address)
	Description (Category)
	Client ID (Needs to be returned after Client is generated)
	
