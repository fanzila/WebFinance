WebFinance API overview
=======================

The API is at v1 and have different schemas that can be viewed at::

    curl -H "Accept: application/json" "http://127.0.0.1:8000/api/v1/?username=YOU_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
    {
      "client": {
          "list_endpoint": "/api/v1/client/", 
          "schema": "/api/v1/client/schema/"
      }, 
      "invoice": {
          "list_endpoint": "/api/v1/invoice/", 
          "schema": "/api/v1/invoice/schema/"
      }, 
      "invoicerows": {
          "list_endpoint": "/api/v1/invoicerows/", 
          "schema": "/api/v1/invoicerows/schema/"
      }, 
      "payinvoice": {
          "list_endpoint": "/api/v1/payinvoice/", 
          "schema": "/api/v1/payinvoice/schema/"
      }, 
      "paysubscription": {
          "list_endpoint": "/api/v1/paysubscription/", 
          "schema": "/api/v1/paysubscription/schema/"
      }, 
      "subscription": {
          "list_endpoint": "/api/v1/subscription/", 
          "schema": "/api/v1/subscription/schema/"
      }, 
      "subscriptionrow": {
          "list_endpoint": "/api/v1/subscriptionrow/", 
          "schema": "/api/v1/subscriptionrow/schema/"
      }
    }
