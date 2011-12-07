#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Dec  2 20:43:27 2011"

from fo.hipay import hipay as HP
from django.core.urlresolvers import reverse
from django.conf import settings
from django.utils.translation import ugettext_lazy as _

def getParams(invoice_id, customer_id, sender_host, internal_transid, payment_type, urls, extra, secure=False,):
    base_host = "http%s://%s" %('s' if secure else '', sender_host)
    # FIXME: All these params are shop parameters, the website might have more
    # than one configured shops itemaccount, taxaccount, insuranceaccount,
    #             fixedcostaccount, shippingcostaccount
    s = HP.PaymentParams(itemaccount=extra.get('itemaccount', None) or settings.HIPAY_ITEMACCOUNT,
                         taxaccount=extra.get('taxaccount', None) or settings.HIPAY_TAXACCOUNT,
                         insuranceaccount=extra.get('insuranceaccount', None) or settings.HIPAY_INSURANCEACCOUNT,
                         fixedcostaccount=extra.get('fixedcostaccount', None) or settings.HIPAY_FIXEDCOSTACCOUNT,
                         shippingcostaccount=extra.get('shippingaccount', None) or settings.HIPAY_SHIPPINGCOSTACCOUNT)
    s.setBackgroundColor(bg_color=extra.get('bg_color', None) or settings.HIPAY_BGCOLOR)
    s.setCaptureDay(extra.get('captureday', None) or settings.HIPAY_CAPTUREDAY)
    s.setCurrency(extra.get('currency', None) or settings.HIPAY_CURRENCY)
    s.setLocale(extra.get('locale', None) or settings.HIPAY_LOCALE)
    s.setEmailAck(extra.get('email_ack', None) or settings.HIPAY_EMAILACK)
    s.setLogin(extra.get('login', None) or settings.HIPAY_LOGIN,
               extra.get('password', None) or settings.HIPAY_PASSWORD)
    s.setMedia(extra.get('media', None) or settings.HIPAY_MEDIA)
    s.setRating(extra.get('rating', None) or settings.HIPAY_RATING)
    s.setIdForMerchant(extra.get('ifformerchant', None) or settings.HIPAY_ID_FOR_MERCHANT)
    s.setMerchantSiteId(extra.get('merchantsiteid', None) or settings.HIPAY_MERCHANT_SITE_ID)
    
    # Will get this back from the IPN ACK 
    s.setMerchantDatas({'invoice_id':invoice_id, 'customer':customer_id})

    URL_OK = urls.get('URL_OK', None) or "%s%s" % (base_host, reverse('hipay_payment_url', kwargs={'payment_type':payment_type,'invoice_id':invoice_id, 'action':'ok', 'internal_transid':internal_transid}))
    URL_NOOK = urls.get('URL_NOOK', None) or "%s%s" % (base_host, reverse('hipay_payment_url', kwargs={'payment_type':payment_type,'invoice_id':invoice_id,'action':'nook', 'internal_transid':internal_transid}))
    URL_CANCEL = urls.get('URL_CANCEL', None) or "%s%s" % (base_host, reverse('hipay_payment_url', kwargs={'payment_type':payment_type,'invoice_id':invoice_id,'action':'cancel', 'internal_transid':internal_transid}))
    URL_ACK = urls.get('URL_ACK', None) or "%s%s" % (base_host, reverse('hipay_ipn_ack', kwargs={'payment_type':payment_type,'invoice_id':invoice_id, 'internal_transid':internal_transid}))
    LOGO_URL = urls.get('LOGO_URL', None) or "%s%s" % (base_host, reverse('hipay_shop_logo'))
    
    s.setURLOk(URL_OK)
    s.setURLNok(URL_NOOK)
    s.setURLCancel(URL_CANCEL)
    s.setURLAck(URL_ACK)
    s.setLogoURL(LOGO_URL)
    return s

def simplepayment(invoice, sender_host, internal_transid, payment_type, secure=False, urls=None, extra=None):
    urls = urls or {}
    extra = extra or {}
    params = getParams(invoice.pk, invoice.client.pk, sender_host, internal_transid, payment_type, urls, extra, secure)

    products = HP.Product()
    taxes = HP.Tax('tax')
    mestax=[dict(taxName='TVA %s'%(invoice.tax,), taxVal=invoice.tax, percentage='true')]
    taxes.setTaxes(mestax)
    list_products = []
    for ligne in invoice.invoicerows_set.all():
        list_products.append({'name':ligne.description,
                         'info':ligne.description,
                         'quantity':int(ligne.qtt),
                         'ref':invoice.num_facture,
                         'category':extra.get('category', None) or settings.HIPAY_DEFAULT_CATEGORY, # https://test-payment.hipay.com/order/list-categories/id/3194
                         'price':ligne.prix_ht,
                         'tax':taxes})
    products.setProducts(list_products)
        
    order = HP.Order()
        
    data = [{'shippingAmount':0,
             'insuranceAmount':0,
             'fixedCostAmount':0, #sum([l.qtt*l.prix_ht for l in invoice.invoicerows_set.all()]),
             'orderTitle':'Facture ISVTEC #%(num_facture)s' %dict(num_facture=invoice.num_facture),
             'orderInfo':'Facture ISVTEC #%(num_facture)s' %dict(num_facture=invoice.num_facture),
             'orderCategory':extra.get('category', None) or settings.HIPAY_DEFAULT_CATEGORY}]
    order.setOrders(data)
    pay = HP.HiPay(params)
    pay.SimplePayment(order, products)
    return pay.SendPayment(settings.HIPAY_GATEWAY)
    

def subscriptionpayment(subscription, sender_host, internal_transid, payment_type, secure=False, urls=None, extra=None):
    urls = urls or {}
    extra = extra or {}

    params = getParams(subscription.pk, subscription.client.pk, sender_host, internal_transid, payment_type, urls, extra, secure)

    price_excl_vat = sum(ligne.price_excl_vat for ligne in  subscription.subscriptionrow_set.all())

    order = HP.Order()

    # FIXME: Introduce Affiliates

    # FIXME: Have to find a better way to format this
    order_info = " | ".join(ligne.description for ligne in  subscription.subscriptionrow_set.all())

    order_data = [{'shippingAmount':0, 'insuranceAmount':0, 'fixedCostAmount':0, 'orderTitle':'Subscription # %s'%subscription.ref_contrat,
                   'orderInfo':order_info,
                   'orderCategory':extra.get('category',None) or settings.HIPAY_DEFAULT_SUBSCRIPTION_CATEGORY},
                  {'shippingAmount':0, 'insuranceAmount':0, 'fixedCostAmount':0,
                   'orderTitle':'Subscription # %s'%subscription.ref_contrat,
                   'orderInfo':order_info,
                   'orderCategory':extra.get('category',None) or settings.HIPAY_DEFAULT_SUBSCRIPTION_CATEGORY}
                   ]
    order.setOrders(order_data)

    inst = HP.Installement()
    mta = HP.Tax('tax')
    mestax=[dict(taxName='TVA %s'%subscription.tax, taxVal=int(subscription.tax), percentage='true')]
    mta.setTaxes(mestax)
    DELAYS = {'monthly':'1M',
              'quarterly':'3M',
              'yearly':'12M'}
    inst_data = [{'price':price_excl_vat, 'first':'true',
                  'paymentDelay': settings.HIPAY_DEFAULT_SUBSCRIPTION_FIRST_PAYMENT_DELAY, #use the field periodic_next_deadline
                  'tax':mta},
                 {'price':price_excl_vat, 'first':'false',
                  'paymentDelay':extra.get('paymentdelay',None) or DELAYS.get(subscription.period, None) or settings.HIPAY_DEFAULT_SUBSCRIPTION_SUBS_PAYMENT_DELAY,
                  'tax':mta}]
    inst.setInstallements(inst_data)

    pay = HP.HiPay(params)
    pay.MultiplePayment(order, inst)

    return pay.SendPayment(settings.HIPAY_GATEWAY)
