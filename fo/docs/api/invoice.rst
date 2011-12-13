Invoice management
==================

Invoice schema
---------------

To get the invoice schema::

  curl   -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/invoice/schema/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"

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
        "account": {
            "blank": false,
            "default": 30,
            "help_text": "Integer data. Ex: 2673",
            "nullable": false,
            "readonly": false,
            "type": "integer",
            "unique": false
        },
        "client": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A single related resource. Can be either a URI or set of nested resource data.",
            "nullable": false,
            "readonly": false,
            "type": "related",
            "unique": false
        },
        "comment": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "date_created": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A date & time as a string. Ex: \"2010-11-10T03:07:43\"",
            "nullable": true,
            "readonly": false,
            "type": "datetime",
            "unique": false
        },
        "date_generated": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A date & time as a string. Ex: \"2010-11-10T03:07:43\"",
            "nullable": true,
            "readonly": false,
            "type": "datetime",
            "unique": false
        },
        "date_sent": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A date & time as a string. Ex: \"2010-11-10T03:07:43\"",
            "nullable": true,
            "readonly": false,
            "type": "datetime",
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
        "down_payment": {
            "blank": false,
            "default": "0",
            "help_text": "Fixed precision numeric data. Ex: 26.73",
            "nullable": true,
            "readonly": false,
            "type": "decimal",
            "unique": false
        },
        "exchange_rate": {
            "blank": false,
            "default": "1.00",
            "help_text": "Fixed precision numeric data. Ex: 26.73",
            "nullable": false,
            "readonly": false,
            "type": "decimal",
            "unique": false
        },
        "extra_bottom": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "extra_top": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        },
        "id": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": true
        },
        "invoice_date": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A date & time as a string. Ex: \"2010-11-10T03:07:43\"",
            "nullable": true,
            "readonly": false,
            "type": "datetime",
            "unique": false
        },
        "invoice_num": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": true
        },
        "invoicerows": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Many related resources. Can be either a list of URIs or list of individually nested resource data.",
            "nullable": true,
            "readonly": false,
            "type": "related",
            "unique": false
        },
        "paid": {
            "blank": false,
            "default": false,
            "help_text": "Boolean data. Ex: True",
            "nullable": true,
            "readonly": false,
            "type": "boolean",
            "unique": false
        },
        "payment_date": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "A date & time as a string. Ex: \"2010-11-10T03:07:43\"",
            "nullable": true,
            "readonly": false,
            "type": "datetime",
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
        "payment_type": {
            "blank": false,
            "default": "",
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
            "nullable": true,
            "readonly": false,
            "type": "datetime",
            "unique": false
        },
        "ref_contract": {
            "blank": false,
            "default": "",
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
        "sent": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Integer data. Ex: 2673",
            "nullable": true,
            "readonly": false,
            "type": "integer",
            "unique": false
        },
        "service_type": {
            "blank": false,
            "default": "No default provided.",
            "help_text": "Integer data. Ex: 2673",
            "nullable": true,
            "readonly": false,
            "type": "integer",
            "unique": false
        },
        "sub_invoice": {
            "blank": false,
            "default": "",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
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
            "default": "facture",
            "help_text": "Unicode string data. Ex: \"Hello World\"",
            "nullable": false,
            "readonly": false,
            "type": "string",
            "unique": false
        }
    }
  }

Invoices list
-------------

To get the list of invoices associated with the current user::

  curl -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/invoice/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
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
            "account": 30,
            "client": "/api/v1/client/1/",
            "comment": "",
            "date_created": "2011-11-10T11:04:59",
            "date_generated": null,
            "date_sent": "2011-11-10T00:00:00",
            "delivery": "email",
            "down_payment": "10.0000",
            "exchange_rate": "1.00",
            "extra_bottom": "",
            "extra_top": "",
            "invoice_date": "2011-11-10T00:00:00",
            "invoice_num": "201111100",
            "invoicerows": [
                {
                    "description": "Test Ousmane",
                    "df_price": "100.00000",
                    "invoice": "/api/v1/invoice/1/",
                    "order": null,
                    "qty": "1.00",
                    "resource_uri": "/api/v1/invoicerows/1/"
                },
                {
                    "description": "Machine virtuelle",
                    "df_price": "250.00000",
                    "invoice": "/api/v1/invoice/1/",
                    "order": null,
                    "qty": "1.00",
                    "resource_uri": "/api/v1/invoicerows/2/"
                },
                {
                    "description": "Deux poules et 4 coqs",
                    "df_price": "6.00000",
                    "invoice": "/api/v1/invoice/1/",
                    "order": null,
                    "qty": "6.00",
                    "resource_uri": "/api/v1/invoicerows/3/"
                }
            ],
            "paid": true,
            "payment_date": "2011-11-10T00:00:00",
            "payment_method": "direct_debit",
            "payment_type": "\u00c3\u20ac r\u00c3\u00a9ception de cette facture",
            "period": "monthly",
            "periodic_next_deadline": "2011-12-10",
            "ref_contract": "150",
            "resource_uri": "/api/v1/invoice/1/",
            "sent": 1,
            "service_type": 5,
            "sub_invoice": "",
            "tax": "19.60",
            "transactions": [],
            "type_doc": "facture"
        },
        ...
        {
            "account": 30,
            "client": "/api/v1/client/1/",
            "comment": "",
            "date_created": "2011-12-13T12:48:00",
            "date_generated": null,
            "date_sent": null,
            "delivery": "email",
            "down_payment": "0.0000",
            "exchange_rate": "1.00",
            "extra_bottom": "",
            "extra_top": "",
            "invoice_date": "2011-11-10T00:00:00",
            "invoice_num": "201112131",
            "invoicerows": [
                {
                    "description": "Premierarticle",
                    "df_price": "17.00000",
                    "invoice": "/api/v1/invoice/75/",
                    "order": null,
                    "qty": "3.00",
                    "resource_uri": "/api/v1/invoicerows/42/"
                },
                {
                    "description": "Deuxi\u00e8me item API",
                    "df_price": "5.00000",
                    "invoice": "/api/v1/invoice/75/",
                    "order": null,
                    "qty": "10.00",
                    "resource_uri": "/api/v1/invoicerows/43/"
                }
            ],
            "paid": false,
            "payment_date": null,
            "payment_method": "unknown",
            "payment_type": "",
            "period": "monthly",
            "periodic_next_deadline": null,
            "ref_contract": "",
            "resource_uri": "/api/v1/invoice/75/",
            "sent": null,
            "service_type": null,
            "sub_invoice": "",
            "tax": "19.60",
            "transactions": [
                {
                    "date": null,
                    "emailClient": null,
                    "first_status": "pending",
                    "idForMerchant": null,
                    "invoice": "/api/v1/invoice/75/",
                    "merchantDatas": null,
                    "not_tempered_with": false,
                    "operation": null,
                    "origAmount": null,
                    "origCurrency": "EUR",
                    "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4ee73ca759926",
                    "refProduct": null,
                    "resource_uri": "/api/v1/payinvoice/11/",
                    "status": null,
                    "subscriptionId": null,
                    "time": null,
                    "transid": null,
                    "url_ack": "http://127.0.0.1:8000/invoice/hipay/test_url_ack"
                }
            ],
            "type_doc": "facture"
        }
    ]
  }

The `transactions` are payments transactions that are recorded in payments
lifetime (authorization, capture, cancel, etc). At any given moment, you can
query a given invoice to get the health of the associated transactions


Invoice instance
----------------

To get the invoice who's resource_uri is /api/v1/invoice/70/::

  curl -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/invoice/70/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  {
    "account": 30,
    "client": "/api/v1/client/1/",
    "comment": "",
    "date_created": "2011-12-06T13:05:33",
    "date_generated": null,
    "date_sent": null,
    "delivery": "email",
    "down_payment": "0.0000",
    "exchange_rate": "1.00",
    "extra_bottom": "",
    "extra_top": "",
    "invoice_date": "2011-11-10T00:00:00",
    "invoice_num": "2011120601",
    "invoicerows": [
        {
            "description": "Premier article",
            "df_price": "17.00000",
            "invoice": "/api/v1/invoice/70/",
            "order": null,
            "qty": "3.00",
            "resource_uri": "/api/v1/invoicerows/32/"
        },
        {
            "description": "Deuxi\u00e8me item",
            "df_price": "5.00000",
            "invoice": "/api/v1/invoice/70/",
            "order": null,
            "qty": "10.00",
            "resource_uri": "/api/v1/invoicerows/33/"
        }
    ],
    "paid": false,
    "payment_date": null,
    "payment_method": "unknown",
    "payment_type": "",
    "period": "monthly",
    "periodic_next_deadline": null,
    "ref_contract": "",
    "resource_uri": "/api/v1/invoice/70/",
    "sent": null,
    "service_type": null,
    "sub_invoice": "",
    "tax": "19.60",
    "transactions": [],
    "type_doc": "devis"
  }

Create Invoice
--------------

To create an invoice you'll have to specify the billed customer since a user
(api_key) may be related to more than one customer::

  curl --dump-header - -H "Content-Type: application/json" -X POST
    --data '{"client":"/api/v1/client/1/",
             "invoice_date":"2011-11-10T00:00:00",
             "invoice_num":"201112132",
             "invoicerows":[{"order": null, "description":"Premierarticle","df_price":17,"qty":3},
                            {"order": null,"description":"Deuxième item API","df_price":5,"qty":10}]}'
    "http://127.0.0.1:8000/api/v1/invoice/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 17:36:23 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/invoice/77/

Update invoice
--------------

To update the invoice just send the data to the resource_uri::

  curl --dump-header - -H "Content-Type: application/json" -X PUT --data
    '{"client":"/api/v1/client/1/", "invoice_date":"2011-11-10T00:00:00", "invoice_num":"201112132",
    "invoicerows":[{"order": 1,"description":"Premier article MAJ","df_price":17,"qty":3},
                   {"order": 2,"description":"Deuxième item API MAJ","df_price":5,"qty":10}]}'
    "http://127.0.0.1:8000/api/v1/invoice/77/?username=YOUR_EMAIL_ADDRESS&api_key=API_KEY"
  HTTP/1.0 204 NO CONTENT
  Date: Tue, 13 Dec 2011 17:42:49 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Length: 0
  Content-Type: text/html; charset=utf-8

  curl -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/invoice/77/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  {
    "account": 30,
    "client": "/api/v1/client/1/",
    "comment": "",
    "date_created": "2011-12-13T18:41:40",
    "date_generated": null,
    "date_sent": null,
    "delivery": "email",
    "down_payment": "0.0000",
    "exchange_rate": "1.00",
    "extra_bottom": "",
    "extra_top": "",
    "invoice_date": "2011-11-10T00:00:00",
    "invoice_num": "201112133",
    "invoicerows": [
        {
            "description": "Premierarticle",
            "df_price": "17.00000",
            "invoice": "/api/v1/invoice/77/",
            "order": null,
            "qty": "3.00",
            "resource_uri": "/api/v1/invoicerows/48/"
        },
        {
            "description": "Deuxi\u00e8me item API",
            "df_price": "5.00000",
            "invoice": "/api/v1/invoice/77/",
            "order": null,
            "qty": "10.00",
            "resource_uri": "/api/v1/invoicerows/49/"
        },
        {
            "description": "Premier article MAJ",
            "df_price": "17.00000",
            "invoice": "/api/v1/invoice/77/",
            "order": null,
            "qty": "3.00",
            "resource_uri": "/api/v1/invoicerows/50/"
        },
        {
            "description": "Deuxi\u00e8me item API MAJ",
            "df_price": "5.00000",
            "invoice": "/api/v1/invoice/77/",
            "order": null,
            "qty": "10.00",
            "resource_uri": "/api/v1/invoicerows/51/"
        }
    ],
    "paid": false,
    "payment_date": null,
    "payment_method": "unknown",
    "payment_type": "",
    "period": "monthly",
    "periodic_next_deadline": null,
    "ref_contract": "",
    "resource_uri": "/api/v1/invoice/77/",
    "sent": null,
    "service_type": null,
    "sub_invoice": "",
    "tax": "19.60",
    "transactions": [],
    "type_doc": "facture"
  }

You'll notice that new invoice rows have been added, if you want to alter an
existing invoice row you'll use the invoicerows schema/resource.

Delete invoice
--------------

  curl --dump-header - -H "Content-Type: application/json" -X DELETE
    "http://127.0.0.1:8000/api/v1/invoice/76/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 204 NO CONTENT
  Date: Tue, 13 Dec 2011 11:41:28 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Length: 0
  Content-Type: text/html; charset=utf-8


  curl --dump-header -  -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/invoice/76/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 404 NOT FOUND
  Date: Tue, 13 Dec 2011 11:42:03 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8

Pay invoice with HiPay
----------------------

To pay an invoice with HiPay you need to create the invoice first and then
reference the invoice to pay::

  curl --dump-header - -H "Content-Type: application/json" -X POST
    --data '{"client":"/api/v1/client/1/",
             "invoice_date":"2011-11-10T00:00:00",
             "invoice_num":"201112132",
             "invoicerows":[{"order": null, "description":"Premierarticle","df_price":17,"qty":3},
                            {"order": null,"description":"Deuxième item API","df_price":5,"qty":10}]}'
    "http://127.0.0.1:8000/api/v1/invoice/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"

  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 11:48:12 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/invoice/77/

  curl --dump-header - -H "Content-Type: application/json" -X POST --data
    '{"invoice":"/api/v1/invoice/77/", "url_ack":"http://127.0.0.1:8000/invoice/hipay/test_url_ack"}'
      "http://127.0.0.1:8000/api/v1/payinvoice/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 11:53:13 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/payinvoice/12/

  curl  -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/payinvoice/11/?username=ousmane%40wilane.org&api_key=fa9149bebb4433f8bbd37cc3b47b971a4fc4f439"
  {
    "date": null,
    "emailClient": null,
    "first_status": "pending",
    "idForMerchant": null,
    "invoice": "/api/v1/invoice/77/",
    "merchantDatas": null,
    "not_tempered_with": false,
    "operation": null,
    "origAmount": null,
    "origCurrency": "EUR",
    "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4ee78f991bcd9",
    "refProduct": null,
    "resource_uri": "/api/v1/payinvoice/12/",
    "status": null,
    "subscriptionId": null,
    "time": null,
    "transid": null,
    "url_ack": "http://127.0.0.1:8000/invoice/hipay/test_url_ack"
  }

You'll notice the redirect_url that allows you to redirect the client to the
HiPay payment gateway. You'll also notice the url_ack that will be called back
when HiPay hit us back with an ACK. Here is a example using Django view::

  @require_http_methods(["POST"])
  @csrf_exempt
  def test_url_ack(request):
      """ This is just a test to show how an ACK would look like from your application"""
      logger.info("Checking the data against the documented postback url")

      url_postback = "http://127.0.0.1:8000%s"%reverse('ack_postback')
      try:
          response = ack_postback(request)
          logger.info(u"Posting back to make sure we get it from the right bot")
      except Exception, e:
          logger.exception(u"Unable to postback %s, we've got an ack message to check %s" %(url_postback, e))
      logger.info(response.read())
      if response == "VERIFIED":
          return HttpResponse("Thanks, getting back to you to check this is you")

      return HttpResponse("May the force be with you")

In this example, your application will hit our postback url to make sure we made
the post request on your url_ack. The post back url will reply VERIFIED or
FAILED and then you'll decide what to do with the data. The Data you receive on
your url_ack is a transaction data with the details from HiPay and the details
we've added (mainly our internal invoice representation as seen in this API
documentation). Here is what we do when we get an ACK from HiPay::

            # Pinging back
            logger.info("Pinging %s for IPN from upstream" %self.url_ack)
            data = serializers.serialize("json", InvoiceTransaction.objects.filter(pk=self.id))
            opener = urllib2.build_opener()
            opener.addheaders = [("Content-Type", "text/json"),
                                 ("Content-Length", str(len(data))),
                                 ("User-Agent", u"ISVTEC -- PAYMENT GATEWAY")]
            urllib2.install_opener(opener)

            request = urllib2.Request(self.url_ack,urlencode({'payment':data}))
            try:
                response = opener.open(request)
                logger.info(u"Pinged back %s ... propagation, got '%s'" %(self.url_ack, response.read()))
            except Exception, e:
                logger.warn(u"Unable to ping back %s, we have an ack to propage: %s" %(self.url_ack, e))

As you can see, your url_ack will receive the variable `payment` POSTed for your
consumption. Once you verify that we made the request, you will be able to
safely cosume the data.


List HiPay invoices payments
----------------------------

You can indeed list all the payments (transactions states) you have access to
with your api_key::

  curl  -H "Accept: application/json"
    "http://127.0.0.1:8000/api/v1/payinvoice/?username=YOU_EMAIL_ADDRESS&api_key=YOU_API_KEY"

  {
    "meta": {
        "limit": 20,
        "next": null,
        "offset": 0,
        "previous": null,
        "total_count": 5
    },
    "objects": [
        {
            "date": null,
            "emailClient": null,
            "first_status": "pending",
            "idForMerchant": null,
            "invoice": "/api/v1/invoice/58/",
            "merchantDatas": null,
            "not_tempered_with": false,
            "operation": null,
            "origAmount": null,
            "origCurrency": "EUR",
            "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4edf9822b4947",
            "refProduct": null,
            "resource_uri": "/api/v1/payinvoice/9/",
            "status": null,
            "subscriptionId": null,
            "time": null,
            "transid": null,
            "url_ack": null
        },
        {
            "date": null,
            "emailClient": null,
            "first_status": "cancel",
            "idForMerchant": null,
            "invoice": "/api/v1/invoice/58/",
            "merchantDatas": null,
            "not_tempered_with": false,
            "operation": null,
            "origAmount": null,
            "origCurrency": "EUR",
            "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4edcadca59ebc",
            "refProduct": null,
            "resource_uri": "/api/v1/payinvoice/7/",
            "status": null,
            "subscriptionId": null,
            "time": null,
            "transid": null,
            "url_ack": null
        },
        {
            "date": null,
            "emailClient": null,
            "first_status": "pending",
            "idForMerchant": null,
            "invoice": "/api/v1/invoice/58/",
            "merchantDatas": null,
            "not_tempered_with": false,
            "operation": null,
            "origAmount": null,
            "origCurrency": "EUR",
            "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4edfaba8a01d2",
            "refProduct": null,
            "resource_uri": "/api/v1/payinvoice/10/",
            "status": null,
            "subscriptionId": null,
            "time": null,
            "transid": null,
            "url_ack": null
        },
        {
            "date": null,
            "emailClient": null,
            "first_status": "pending",
            "idForMerchant": null,
            "invoice": "/api/v1/invoice/75/",
            "merchantDatas": null,
            "not_tempered_with": false,
            "operation": null,
            "origAmount": null,
            "origCurrency": "EUR",
            "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4ee73ca759926",
            "refProduct": null,
            "resource_uri": "/api/v1/payinvoice/11/",
            "status": null,
            "subscriptionId": null,
            "time": null,
            "transid": null,
            "url_ack": "http://127.0.0.1:8000/invoice/hipay/test_url_ack"
        },
        {
            "date": null,
            "emailClient": null,
            "first_status": "pending",
            "idForMerchant": null,
            "invoice": "/api/v1/invoice/77/",
            "merchantDatas": null,
            "not_tempered_with": false,
            "operation": null,
            "origAmount": null,
            "origCurrency": "EUR",
            "redirect_url": "https://test-payment.hipay.com/index/mapi/id/4ee78f991bcd9",
            "refProduct": null,
            "resource_uri": "/api/v1/payinvoice/12/",
            "status": null,
            "subscriptionId": null,
            "time": null,
            "transid": null,
            "url_ack": "http://127.0.0.1:8000/invoice/hipay/test_url_ack"
        }
    ]
  }

You see that the invoice /api/v1/invoice/58/ have had too payments attempts that
didn't get past the first step of payment process (client acceptation).
