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
        "accompte": {
            "blank": false, 
            "default": "0", 
            "help_text": "Fixed precision numeric data. Ex: 26.73", 
            "nullable": true, 
            "readonly": false, 
            "type": "decimal", 
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
        "commentaire": {
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
        "date_facture": {
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
        "date_paiement": {
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
        "facture_file": {
            "blank": false, 
            "default": "", 
            "help_text": "Unicode string data. Ex: \"Hello World\"", 
            "nullable": false, 
            "readonly": false, 
            "type": "string", 
            "unique": false
        }, 
        "id_compte": {
            "blank": false, 
            "default": 30, 
            "help_text": "Integer data. Ex: 2673", 
            "nullable": false, 
            "readonly": false, 
            "type": "integer", 
            "unique": false
        }, 
        "id_facture": {
            "blank": false, 
            "default": "", 
            "help_text": "Unicode string data. Ex: \"Hello World\"", 
            "nullable": false, 
            "readonly": false, 
            "type": "string", 
            "unique": true
        }, 
        "id_type_presta": {
            "blank": false, 
            "default": "No default provided.", 
            "help_text": "Integer data. Ex: 2673", 
            "nullable": true, 
            "readonly": false, 
            "type": "integer", 
            "unique": false
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
        "is_envoye": {
            "blank": false, 
            "default": "No default provided.", 
            "help_text": "Integer data. Ex: 2673", 
            "nullable": true, 
            "readonly": false, 
            "type": "integer", 
            "unique": false
        }, 
        "is_paye": {
            "blank": false, 
            "default": false, 
            "help_text": "Boolean data. Ex: True", 
            "nullable": true, 
            "readonly": false, 
            "type": "boolean", 
            "unique": false
        }, 
        "num_facture": {
            "blank": false, 
            "default": "", 
            "help_text": "Unicode string data. Ex: \"Hello World\"", 
            "nullable": false, 
            "readonly": false, 
            "type": "string", 
            "unique": true
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
            "nullable": true, 
            "readonly": false, 
            "type": "datetime", 
            "unique": false
        }, 
        "ref_contrat": {
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
        }, 
        "type_paiement": {
            "blank": false, 
            "default": "", 
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
        "total_count": 14
    }, 
    "objects": [
        {
            "accompte": "10.0000", 
            "client": "/api/v1/client/1/", 
            "commentaire": "", 
            "date_created": "2011-11-10T11:04:59", 
            "date_facture": "2011-11-10T00:00:00", 
            "date_generated": null, 
            "date_paiement": "2011-11-10T00:00:00", 
            "date_sent": "2011-11-10T00:00:00", 
            "delivery": "email", 
            "exchange_rate": "1.00", 
            "extra_bottom": "", 
            "extra_top": "", 
            "facture_file": "", 
            "id_compte": 30, 
            "id_facture": "1", 
            "id_type_presta": 5, 
            "invoicerows": [
                {
                    "description": "Test Ousmane", 
                    "id_facture": "/api/v1/invoice/1/", 
                    "id_facture_ligne": "1", 
                    "ordre": null, 
                    "prix_ht": "100.00000", 
                    "qtt": "1.00", 
                    "resource_uri": "/api/v1/invoicerows/1/"
                }, 
                {
                    "description": "Machine virtuelle", 
                    "id_facture": "/api/v1/invoice/1/", 
                    "id_facture_ligne": "2", 
                    "ordre": null, 
                    "prix_ht": "250.00000", 
                    "qtt": "1.00", 
                    "resource_uri": "/api/v1/invoicerows/2/"
                }, 
                {
                    "description": "Deux poules et 4 coqs", 
                    "id_facture": "/api/v1/invoice/1/", 
                    "id_facture_ligne": "3", 
                    "ordre": null, 
                    "prix_ht": "6.00000", 
                    "qtt": "6.00", 
                    "resource_uri": "/api/v1/invoicerows/3/"
                }
            ], 
            "is_envoye": 1, 
            "is_paye": true, 
            "num_facture": "201111100", 
            "payment_method": "direct_debit", 
            "period": "monthly", 
            "periodic_next_deadline": "2011-12-10", 
            "ref_contrat": "150", 
            "resource_uri": "/api/v1/invoice/1/", 
            "tax": "19.60", 
            "transactions": [], 
            "type_doc": "facture", 
            "type_paiement": "\u00c3\u20ac r\u00c3\u00a9ception de cette facture"
        }, 
        ...
        {
            "accompte": "0.0000", 
            "client": "/api/v1/client/1/", 
            "commentaire": "", 
            "date_created": "2011-12-06T13:05:33", 
            "date_facture": "2011-11-10T00:00:00", 
            "date_generated": null, 
            "date_paiement": null, 
            "date_sent": null, 
            "delivery": "email", 
            "exchange_rate": "1.00", 
            "extra_bottom": "", 
            "extra_top": "", 
            "facture_file": "", 
            "id_compte": 30, 
            "id_facture": "70", 
            "id_type_presta": null, 
            "invoicerows": [
                {
                    "description": "Premier article", 
                    "id_facture": "/api/v1/invoice/70/", 
                    "id_facture_ligne": "32", 
                    "ordre": null, 
                    "prix_ht": "17.00000", 
                    "qtt": "3.00", 
                    "resource_uri": "/api/v1/invoicerows/32/"
                }, 
                {
                    "description": "Deuxi\u00e8me item", 
                    "id_facture": "/api/v1/invoice/70/", 
                    "id_facture_ligne": "33", 
                    "ordre": null, 
                    "prix_ht": "5.00000", 
                    "qtt": "10.00", 
                    "resource_uri": "/api/v1/invoicerows/33/"
                }
            ], 
            "is_envoye": null, 
            "is_paye": false, 
            "num_facture": "2011120601", 
            "payment_method": "unknown", 
            "period": "monthly", 
            "periodic_next_deadline": null, 
            "ref_contrat": "", 
            "resource_uri": "/api/v1/invoice/70/", 
            "tax": "19.60", 
            "transactions": [], 
            "type_doc": "devis", 
            "type_paiement": ""
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
    "accompte": "0.0000", 
    "client": "/api/v1/client/1/", 
    "commentaire": "", 
    "date_created": "2011-12-06T13:05:33", 
    "date_facture": "2011-11-10T00:00:00", 
    "date_generated": null, 
    "date_paiement": null, 
    "date_sent": null, 
    "delivery": "email", 
    "exchange_rate": "1.00", 
    "extra_bottom": "", 
    "extra_top": "", 
    "facture_file": "", 
    "id_compte": 30, 
    "id_facture": "70", 
    "id_type_presta": null, 
    "invoicerows": [
        {
            "description": "Premier article", 
            "id_facture": "/api/v1/invoice/70/", 
            "id_facture_ligne": "32", 
            "ordre": null, 
            "prix_ht": "17.00000", 
            "qtt": "3.00", 
            "resource_uri": "/api/v1/invoicerows/32/"
        }, 
        {
            "description": "Deuxi\u00e8me item", 
            "id_facture": "/api/v1/invoice/70/", 
            "id_facture_ligne": "33", 
            "ordre": null, 
            "prix_ht": "5.00000", 
            "qtt": "10.00", 
            "resource_uri": "/api/v1/invoicerows/33/"
        }
    ], 
    "is_envoye": null, 
    "is_paye": false, 
    "num_facture": "2011120601", 
    "payment_method": "unknown", 
    "period": "monthly", 
    "periodic_next_deadline": null, 
    "ref_contrat": "", 
    "resource_uri": "/api/v1/invoice/70/", 
    "tax": "19.60", 
    "transactions": [], 
    "type_doc": "devis", 
    "type_paiement": ""
  }

Create Invoice
--------------

To create an invoice you'll have to specify the billed customer since a user
(api_key) may be related to more than one customer::

  curl --dump-header - -H "Content-Type: application/json" -X POST 
    --data '{"client":"/api/v1/client/1/", 
             "date_facture":"2011-11-10T00:00:00", 
             "num_facture":"201112131", 
             "invoicerows":[{"ordre": null, "description":"Premierarticle","prix_ht":17,"qtt":3},
                            {"ordre": null,"description":"Deuxième item API","prix_ht":5,"qtt":10}]}' 
    "http://127.0.0.1:8000/api/v1/invoice/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 11:30:11 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/invoice/74/

Update invoice
--------------

To update the invoice just send the data to the resource_uri::

  curl --dump-header - -H "Content-Type: application/json" -X PUT --data 
    '{"client":"/api/v1/client/1/", "date_facture":"2011-11-10T00:00:00", "num_facture":"201112131", 
    "invoicerows":[{"ordre": 1,"description":"Premier article MAJ","prix_ht":17,"qtt":3},
                   {"ordre": 2,"description":"Deuxième item API MAJ","prix_ht":5,"qtt":10}]}'
    "http://127.0.0.1:8000/api/v1/invoice/74/?username=YOUR_EMAIL_ADDRESS&api_key=API_KEY"
  HTTP/1.0 204 NO CONTENT
  Date: Tue, 13 Dec 2011 11:35:18 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Length: 0
  Content-Type: text/html; charset=utf-8

  curl -H "Accept: application/json" 
    "http://127.0.0.1:8000/api/v1/invoice/74/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  {
    "accompte": "0.0000", 
    "client": "/api/v1/client/1/", 
    "commentaire": "", 
    "date_created": "2011-12-13T12:30:00", 
    "date_facture": "2011-11-10T00:00:00", 
    "date_generated": null, 
    "date_paiement": null, 
    "date_sent": null, 
    "delivery": "email", 
    "exchange_rate": "1.00", 
    "extra_bottom": "", 
    "extra_top": "", 
    "facture_file": "", 
    "id_compte": 30, 
    "id_facture": "74", 
    "id_type_presta": null, 
    "invoicerows": [
        {
            "description": "Premierarticle", 
            "id_facture": "/api/v1/invoice/74/", 
            "id_facture_ligne": "38", 
            "ordre": null, 
            "prix_ht": "17.00000", 
            "qtt": "3.00", 
            "resource_uri": "/api/v1/invoicerows/38/"
        }, 
        {
            "description": "Deuxi\u00e8me item API", 
            "id_facture": "/api/v1/invoice/74/", 
            "id_facture_ligne": "39", 
            "ordre": null, 
            "prix_ht": "5.00000", 
            "qtt": "10.00", 
            "resource_uri": "/api/v1/invoicerows/39/"
        }, 
        {
            "description": "Premier article MAJ", 
            "id_facture": "/api/v1/invoice/74/", 
            "id_facture_ligne": "40", 
            "ordre": 1, 
            "prix_ht": "17.00000", 
            "qtt": "3.00", 
            "resource_uri": "/api/v1/invoicerows/40/"
        }, 
        {
            "description": "Deuxi\u00e8me item API MAJ", 
            "id_facture": "/api/v1/invoice/74/", 
            "id_facture_ligne": "41", 
            "ordre": 2, 
            "prix_ht": "5.00000", 
            "qtt": "10.00", 
            "resource_uri": "/api/v1/invoicerows/41/"
        }
    ], 
    "is_envoye": null, 
    "is_paye": false, 
    "num_facture": "201112131", 
    "payment_method": "unknown", 
    "period": "monthly", 
    "periodic_next_deadline": null, 
    "ref_contrat": "", 
    "resource_uri": "/api/v1/invoice/74/", 
    "tax": "19.60", 
    "transactions": [], 
    "type_doc": "facture", 
    "type_paiement": ""
  }

You'll notice that new invoice rows have been added, if you want to alter an
existing invoice row you'll use the invoicerows schema/resource.

Delete invoice
--------------

  curl --dump-header - -H "Content-Type: application/json" -X DELETE 
    "http://127.0.0.1:8000/api/v1/invoice/74/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 204 NO CONTENT
  Date: Tue, 13 Dec 2011 11:41:28 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Length: 0
  Content-Type: text/html; charset=utf-8


  curl --dump-header -  -H "Accept: application/json" 
    "http://127.0.0.1:8000/api/v1/invoice/74/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
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
             "date_facture":"2011-11-10T00:00:00", 
             "num_facture":"201112131", 
             "invoicerows":[{"ordre": null, "description":"Premierarticle","prix_ht":17,"qtt":3},
                            {"ordre": null,"description":"Deuxième item API","prix_ht":5,"qtt":10}]}' 
    "http://127.0.0.1:8000/api/v1/invoice/?username=YOUR_EMAIL_ADDRESS&api_key=YOUR_API_KEY"

  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 11:48:12 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/invoice/75/

  curl --dump-header - -H "Content-Type: application/json" -X POST --data 
    '{"invoice":"/api/v1/invoice/75/", "url_ack":"http://127.0.0.1:8000/invoice/hipay/test_url_ack"}' 
      "http://127.0.0.1:8000/api/v1/payinvoice/?username=YOU_EMAIL_ADDRESS&api_key=YOUR_API_KEY"
  HTTP/1.0 201 CREATED
  Date: Tue, 13 Dec 2011 11:53:13 GMT
  Server: WSGIServer/0.1 Python/2.7.1
  Vary: Cookie
  Content-Type: text/html; charset=utf-8
  Location: http://127.0.0.1:8000/api/v1/payinvoice/11/

  curl  -H "Accept: application/json" 
    "http://127.0.0.1:8000/api/v1/payinvoice/11/?username=ousmane%40wilane.org&api_key=fa9149bebb4433f8bbd37cc3b47b971a4fc4f439"
  {
    "date": null, 
    "emailClient": null, 
    "first_status": "pending", 
    "id": "11", 
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
