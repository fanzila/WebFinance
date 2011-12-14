Subscription management
=======================

The subscriptions are actually handled internally using invoices due to some
limitations on HiPay installments management from MAPI (unable to change the
subscriptions or delete them from MAPI). When the HiPay MAPI get the missing
features, the management from the API will be transparent.

Subscription schema
-------------------

To get the subscription schema::

  curl  -H "Accept: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        "http://127.0.0.1:8000/api/v1/subscription/schema/" |python -m json.tool
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
        "client": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A single related resource. Can be either a URI or set of nested resource data.",
            "nullable": false,
            "readonly": false,
            "type": "related",
            "unique": false
        },
        "delivery": {
            "blank": false,
            "default": "email",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "payment_method": {
            "blank": false,
            "default": "unknown",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "period": {
            "blank": false,
            "default": "monthly",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "periodic_next_deadline": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A date & time as a string. Ex: \"2010-11-10T03:07:43\"",
            "nullable": false,
            "readonly": false,
            "type": "datetime",
            "unique": false
        },
        "ref_contrat": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
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
        "subscriptionrows": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Many related resources. Can be either a list of URIs or list of individually nested resource data.",
            "nullable": true,
            "readonly": false,
            "type": "related",
            "unique": false
        },
        "tax": {
            "blank": false,
            "default": "19.60",
            "help_text": "Fixed precision numeric data. Ex: 26.73",
            "nullable": false,
            "readonly": false,
            "type": "decimal",
            "unique": false
        },
        "transactions": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Many related resources. Can be either a list of URIs or list of individually nested resource data.",
            "nullable": true,
            "readonly": false,
            "type": "related",
            "unique": false
        },
        "type_doc": {
            "blank": false,
            "default": "invoice",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        }
    }
  }


Subscriptions list
------------------

To get the list of subscriptions associated with the current user::

  curl  -H "Accept: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        "http://127.0.0.1:8000/api/v1/subscription/" |python -m json.tool
  {
    "meta": {
        "limit": 20,
        "next": null,
        "offset": 0,
        "previous": null,
        "total_count": 1
    },
    "objects": [
        {
            "client": "/api/v1/client/1/",
            "delivery": "email",
            "payment_method": "unknown",
            "period": "monthly",
            "periodic_next_deadline": "2011-12-05",
            "ref_contrat": "0412201101",
            "resource_uri": "/api/v1/subscription/1/",
            "subscriptionrows": [
                {
                    "description": "Premier article",
                    "price_excl_vat": "17.00000",
                    "qty": "3.00",
                    "resource_uri": "/api/v1/subscriptionrow/1/",
                    "subscription": "/api/v1/subscription/1/"
                },
                {
                    "description": "Deuxi\u00e8me item",
                    "price_excl_vat": "5.00000",
                    "qty": "10.00",
                    "resource_uri": "/api/v1/subscriptionrow/2/",
                    "subscription": "/api/v1/subscription/1/"
                }
            ],
            "tax": "19.60",
            "transactions": [
                {
                    "date": "2011-12-10",
                    "emailClient": "ousmane@wilane.net",
                    "first_status": "pending",
                    "idForMerchant": "142545",
                    "merchantDatas": "{'internal_transid': '8'}",
                    "not_tempered_with": false,
                    "operation": "capture",
                    "origAmount": "26.18",
                    "origCurrency": "EUR",
                    "redirect_url": null,
                    "refProduct": null,
                    "resource_uri": "/api/v1/paysubscription/10/",
                    "status": "ok",
                    "subscription": "/api/v1/subscription/1/",
                    "subscriptionId": "E4B518D4AD118310C533FC0416E51619",
                    "time": "11:24:00",
                    "transid": "4EE1EFC209BD0",
                    "url_ack": null
                },
                {
                    "date": null,
                    "emailClient": null,
                    "first_status": "ok",
                    "idForMerchant": null,
                    "merchantDatas": null,
                    "not_tempered_with": false,
                    "operation": null,
                    "origAmount": null,
                    "origCurrency": "EUR",
                    "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4ee1ef1a2330f",
                    "refProduct": null,
                    "resource_uri": "/api/v1/paysubscription/8/",
                    "status": null,
                    "subscription": "/api/v1/subscription/1/",
                    "subscriptionId": null,
                    "time": null,
                    "transid": null,
                    "url_ack": null
                },
                {
                    "date": "2011-12-09",
                    "emailClient": "ousmane@wilane.net",
                    "first_status": "pending",
                    "idForMerchant": "142545",
                    "merchantDatas": "{'internal_transid': '8'}",
                    "not_tempered_with": false,
                    "operation": "authorization",
                    "origAmount": "26.18",
                    "origCurrency": "EUR",
                    "redirect_url": null,
                    "refProduct": null,
                    "resource_uri": "/api/v1/paysubscription/9/",
                    "status": "ok",
                    "subscription": "/api/v1/subscription/1/",
                    "subscriptionId": "E4B518D4AD118310C533FC0416E51619",
                    "time": "11:23:04",
                    "transid": "4EE1EFC209BD0",
                    "url_ack": null
                }
            ],
            "type_doc": "invoice"
        }
    ]
  }

The `transactions` are payments transactions that are recorded in subscription
payments lifetime (authorization, capture, cancel, etc for each term). At any
given moment, you can query a given subscription to get the health of the
associated transactions.

Subscription instance
---------------------

To get the subscription who's resource_uri is /api/v1/invoice/1/::

  curl  -H "Accept: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        "http://127.0.0.1:8000/api/v1/subscription/1/" |python -m json.tool
  {
    "client": "/api/v1/client/1/",
    "delivery": "email",
    "payment_method": "unknown",
    "period": "monthly",
    "periodic_next_deadline": "2011-12-05",
    "ref_contrat": "0412201101",
    "resource_uri": "/api/v1/subscription/1/",
    "subscriptionrows": [
        {
            "description": "Premier article",
            "price_excl_vat": "17.00000",
            "qty": "3.00",
            "resource_uri": "/api/v1/subscriptionrow/1/",
            "subscription": "/api/v1/subscription/1/"
        },
        {
            "description": "Deuxi\u00e8me item",
            "price_excl_vat": "5.00000",
            "qty": "10.00",
            "resource_uri": "/api/v1/subscriptionrow/2/",
            "subscription": "/api/v1/subscription/1/"
        }
    ],
    "tax": "19.60",
    "transactions": [
        {
            "date": "2011-12-10",
            "emailClient": "ousmane@wilane.net",
            "first_status": "pending",
            "idForMerchant": "142545",
            "merchantDatas": "{'internal_transid': '8'}",
            "not_tempered_with": false,
            "operation": "capture",
            "origAmount": "26.18",
            "origCurrency": "EUR",
            "redirect_url": null,
            "refProduct": null,
            "resource_uri": "/api/v1/paysubscription/10/",
            "status": "ok",
            "subscription": "/api/v1/subscription/1/",
            "subscriptionId": "E4B518D4AD118310C533FC0416E51619",
            "time": "11:24:00",
            "transid": "4EE1EFC209BD0",
            "url_ack": null
        },
        {
            "date": null,
            "emailClient": null,
            "first_status": "ok",
            "idForMerchant": null,
            "merchantDatas": null,
            "not_tempered_with": false,
            "operation": null,
            "origAmount": null,
            "origCurrency": "EUR",
            "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4ee1ef1a2330f",
            "refProduct": null,
            "resource_uri": "/api/v1/paysubscription/8/",
            "status": null,
            "subscription": "/api/v1/subscription/1/",
            "subscriptionId": null,
            "time": null,
            "transid": null,
            "url_ack": null
        },
        {
            "date": "2011-12-09",
            "emailClient": "ousmane@wilane.net",
            "first_status": "pending",
            "idForMerchant": "142545",
            "merchantDatas": "{'internal_transid': '8'}",
            "not_tempered_with": false,
            "operation": "authorization",
            "origAmount": "26.18",
            "origCurrency": "EUR",
            "redirect_url": null,
            "refProduct": null,
            "resource_uri": "/api/v1/paysubscription/9/",
            "status": "ok",
            "subscription": "/api/v1/subscription/1/",
            "subscriptionId": "E4B518D4AD118310C533FC0416E51619",
            "time": "11:23:04",
            "transid": "4EE1EFC209BD0",
            "url_ack": null
        }
    ],
    "type_doc": "invoice"
  }

Create subscription
-------------------

To create a subscription you'll have to specify the billed customer since a user
(api_key) may be related to more than one customer::

  curl --dump-header -
        -H "Content-Type: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        -X POST
        --data '{"client":"/api/v1/client/1/",
             "periodic_next_deadline":"2012-01-14T00:00:00",
             "ref_contrat":"201112132",
             "subscriptionrows":[{"description":"Premier article","price_excl_vat":17,"qty":3},
                                 {"description":"Deuxième item API","price_excl_vat":5,"qty":10}]}'
        "http://127.0.0.1:8000/api/v1/subscription/"

  HTTP/1.0 201 CREATED
  Date: Wed, 14 Dec 2011 17:41:26 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/subscription/2/

Update subscription
-------------------

To update a subscription just send the data to the resource_uri::

  curl --dump-header -
        -H "Content-Type: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        -X PUT
        --data '{"client":"/api/v1/client/1/",
             "periodic_next_deadline":"2012-01-14T00:00:00",
             "ref_contrat":"201112132"}'
        "http://127.0.0.1:8000/api/v1/subscription/2/"

  HTTP/1.0 204 NO CONTENT
  Date: Wed, 14 Dec 2011 17:47:57 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Length: 0
  Content-Type: text/html; charset=utf-8

  curl
        -H "Accept: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        "http://127.0.0.1:8000/api/v1/subscription/2/" |python -m json.tool
  {
    "client": "/api/v1/client/1/",
    "delivery": "email",
    "payment_method": "unknown",
    "period": "monthly",
    "periodic_next_deadline": "2012-01-14",
    "ref_contrat": "201112133",
    "resource_uri": "/api/v1/subscription/2/",
    "subscriptionrows": [
        {
            "description": "Premier article",
            "price_excl_vat": "17.00000",
            "qty": "3.00",
            "resource_uri": "/api/v1/subscriptionrow/3/",
            "subscription": "/api/v1/subscription/2/"
        },
        {
            "description": "Deuxi\u00e8me item API",
            "price_excl_vat": "5.00000",
            "qty": "10.00",
            "resource_uri": "/api/v1/subscriptionrow/4/",
            "subscription": "/api/v1/subscription/2/"
        }
    ],
    "tax": "19.60",
    "transactions": [],
    "type_doc": "invoice"
  }

Delete a subscription
---------------------

Delete a subscription is easy::

  curl --dump-header -
        -H "Content-Type: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        -X DELETE
        "http://127.0.0.1:8000/api/v1/subscription/2/"


Pay a subscription with HiPay (installment)
-------------------------------------------

To install a subscription on HiPay you need to create the subscription first and
then reference the subscription to pay::

  curl --dump-header -
        -H "Content-Type: application/json"
        -H "username: YOUR_USERNAME"
        -H "api_key: YOUR_API_KEY"
        -X POST
       '{"subscription":"/api/v1/subscription/77/", "url_ack":"http://127.0.0.1:8000/subscription/hipay/test_url_ack"}'
        "http://127.0.0.1:8000/api/v1/paysubscription/"

See :ref:`pay-invoice-reference` for more details on the url_ack, redirect_url
parameters and the post back process.
