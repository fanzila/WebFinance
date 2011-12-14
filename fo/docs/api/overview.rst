WebFinance API overview
=======================

The API is at v1 and have different schema that can be viewed at::

    curl -H "Accept: application/json" "http://127.0.0.1:8000/api/v1/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
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

Instead of using GET to pass the api key authentication, you can also add them
to POSTed data if you're POSTing or send them as regular http headers (be
careful some client will mangle the headers on the fly)::

  curl  -H "Accept: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        "http://127.0.0.1:8000/api/v1/client/" |python -m json.tool
  {
    "meta": {
        "limit": 20,
        "next": null,
        "offset": 0,
        "previous": null,
        "total_count": 16
    },
    "objects": [
        {
            "addr1": "1 rue Emile Zola",
            "addr2": "",
            "addr3": "",
            "city": "LYON",
            "country": "FR",
            "email": "cyril@bouthors.org",
            "fax": "",
            "name": "ISVTEC",
            "resource_uri": "/api/v1/client/1/",
            "siren": "",
            "tel": "775693504",
            "vat_number": "10000",
            "web": "http://",
            "zip": "69002"
        },
        ...
        {
            "addr1": "Nowhere",
            "addr2": "",
            "addr3": "",
            "city": "ben ville",
            "country": "",
            "email": "",
            "fax": "",
            "name": "Foo Baz",
            "resource_uri": "/api/v1/client/14543/",
            "siren": "",
            "tel": "",
            "vat_number": null,
            "web": "",
            "zip": ""
        }
    ]
  }
