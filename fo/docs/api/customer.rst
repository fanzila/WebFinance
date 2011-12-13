Customer management
===================

Customer schema
---------------

To get the customer schema::

  curl -H "Accept: application/json" "http://127.0.0.1:8000/api/v1/client/schema/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"

  {
    "allowed_detail_http_methods": [
        "get",
        "post",
        "put",
        "delete",
        "patch"
    ],
    "allowed_list_http_methods": [
        "get",
        "post",
        "put",
        "delete",
        "patch"
    ],
    "default_format": "application/json",
    "default_limit": 20,
    "fields": {
        "addr1": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "addr2": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "addr3": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "city": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "country": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "email": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "fax": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "name": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": true
        },
        "resource_uri": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": true,
            "type": "string",
            "unique": false
        },
        "siren": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "tel": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "vat_number": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": true,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "web": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "zip": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        }
    }
  }

Customer list
-------------

To get the list of customers associated with the current user::

  curl -H "Accept: application/json" "http://127.0.0.1:8000/api/v1/client/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  {
    "meta": {
        "limit": 20,
        "next": null,
        "offset": 0,
        "previous": null,
        "total_count": 15
    },
    "objects": [
        {
            "addr1": "1 rue Emile Zola",
            "addr2": "",
            "addr3": "",
            "zip": "69002",
            "city": "LYON",
            "country": "FR",
            "email": "cyril@bouthors.org",
            "fax": "",
            "name": "ISVTEC",
            "resource_uri": "/api/v1/client/1/",
            "siren": "",
            "tel": "775693504",
            "vat_number": "10000",
            "web": "http://"
        },
        ...
        {
            "addr1": "Dakar Libert\u00e9",
            "addr2": "",
            "addr3": "",
            "zip": "12345",
            "city": "Dakar",
            "country": "SN",
            "email": "ousmane@wilane.org",
            "fax": "",
            "name": "Arc",
            "resource_uri": "/api/v1/client/14541/",
            "siren": "",
            "tel": "",
            "vat_number": "",
            "web": ""
        }
    ]
  }

Customer instance
-----------------
To get the customer who's resource_uri is /api/v1/client/14541::

  curl -H "Accept: application/json" "http://127.0.0.1:8000/api/v1/client/14541/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  {
    "addr1": "Dakar Libert\u00e9",
    "addr2": "",
    "addr3": "",
    "city": "Dakar",
    "country": "SN",
    "email": "ousmane@wilane.org",
    "fax": "",
    "name": "Arc",
    "resource_uri": "/api/v1/client/14541/",
    "siren": "",
    "tel": "",
    "vat_number": "",
    "web": "",
    "zip": "12345"
  }

Create Customer
---------------
The created object is returned in the location header::

  curl --dump-header - -H "Content-Type: application/json"
       -X POST --data '{"name":"Foo Baz", "addr1":"Nowhere", "city":"ben ville"}'
       "http://127.0.0.1:8000/api/v1/client/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"

  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 14:53:49 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/client/14543/


Update Customer
---------------
To update just `PUT` the fields to be updated to the ressource_uri of the instance::

  curl --dump-header - -H "Content-Type: application/json"
    -X PUT --data '{"name":"Foo Baz", "addr1":"Nowhere", "city":"ben ville", "country":"SN"}'
    "http://127.0.0.1:8000/api/v1/client/14543/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 204 NO CONTENT
  Date: Tue, 13 Dec 2011 10:16:07 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Length: 0
  Content-Type: text/html; charset=utf-8

  curl -H "Accept: application/json" "http://127.0.0.1:8000/api/v1/client/14543/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
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

Delete customer
---------------

All the related data will be deleted too (invoices, subscriptions, etc). We'll
create an invoice for this customer and then delete customer and finally request
the customer using the resource_uri::

  curl --dump-header - -H "Content-Type: application/json" -X POST
    --data '{"client":"/api/v1/client/14543/",
             "date_facture":"2011-11-10T00:00:00",
             "num_facture":"201112131",
             "invoicerows":[{"ordre": null, "description":"Premierarticle","prix_ht":17,"qtt":3},
                            {"ordre": null,"description":"Deuxième item API","prix_ht":5,"qtt":10}]}'
    "http://127.0.0.1:8000/api/v1/invoice/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 10:46:11 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/invoice/73/

  curl --dump-header - -H "Content-Type: application/json" -X DELETE
    "http://127.0.0.1:8000/api/v1/client/14543/?username=ousmane%40YOUR_EMAIL_ADDRESS=YOUR_API_KEY"
  HTTP/1.0 204 NO CONTENT
  Date: Tue, 13 Dec 2011 10:47:37 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Length: 0
  Content-Type: text/html; charset=utf-8

  curl --dump-header -  -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/client/14543/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 404 NOT FOUND
  Date: Tue, 13 Dec 2011 10:48:15 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
