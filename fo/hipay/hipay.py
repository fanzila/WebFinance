#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Wed Nov 16 07:40:23 2011"

import re
import os
import urllib2
from urllib import urlencode
import hashlib
import xml.etree.ElementTree as ET
#FIXME: use regular gettext to get this library Django independant or make it a
#full django app django-hipay
from django.utils.translation import ugettext_lazy as _
from xml.dom import minidom
from lxml.etree import XMLSchema, XMLParser, fromstring, _Element
DIRNAME = os.path.dirname(__file__)

# Borrowed from Django to avoid django dependency for those who whish to use the
# library standalone
URL_RE = re.compile(
    r'^(?:http|ftp)s?://' # http:// or https://
    r'(?:(?:[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?\.)+(?:[A-Z]{2,6}\.?|[A-Z0-9-]{2,}\.?)|' #domain...
    r'localhost|' #localhost...
    r'\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})' # ...or ip
    r'(?::\d+)?' # optional port
    r'(?:/?|[/?]\S+)$', re.IGNORECASE)
EMAIL_RE = re.compile(
    r"(^[-!#$%&'*+/=?^_`{}|~0-9A-Z]+(\.[-!#$%&'*+/=?^_`{}|~0-9A-Z]+)*"  # dot-atom
    r'|^"([\001-\010\013\014\016-\037!#-\[\]-\177]|\\[\001-\011\013\014\016-\177])*"' # quoted-string
    r')@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$', re.IGNORECASE)  # domain
    
def FOElement(element):
    """Backport of the method from 2.7"""
    ret = ET.Element(element)
    ret.__class__.extend = lambda self, elements: self._children.extend(elements)
    return ret
        
def setTag(tags, etree, attrs=None):
    # FIXME: Use the attrs if any
    for key, value in tags.iteritems():
        if getattr(value, 'asTree', False):
            etree.append(value.asTree().getroot())
            continue
        tag =  FOElement(key)
        tag.text = unicode(value)
        if etree.find(key) is not None:
            etree.find(key).text = unicode(value)
        else:
            etree.append(tag)
    return etree

def is_number(s):
    try:
        float(s)
        return True
    except ValueError:
        return False

class HiPayTree(object):
    def __init__(self):
        self.root = FOElement('root')

    def asTree(self):
        return ET.ElementTree(self.root)

    def validate(self, xml=None):
        """Validate against xsd schema the provided schema
        https://payment.hipay.com/schema/mapi.xsd"""
        schema = XMLSchema(file=open(os.path.join(DIRNAME, 'mapi.xsd'), 'rb'), attribute_defaults=True)
        parser = XMLParser(schema=schema, attribute_defaults=True)        
        if not xml:
            xml = ET.tostring(self.asTree().getroot())
        try:
            root = fromstring(xml, parser)
        except Exception, e:
            return False
        return isinstance(root, _Element)
        

    def prettify(self):
        """Return a pretty-printed XML string for the Element.
        """
        elem = self.root
        rough_string = ET.tostring(elem, 'utf-8')
        reparsed = minidom.parseString(rough_string)
        return reparsed.toprettyxml(indent="  ")    
    

class PaymentParams(HiPayTree):
    def __init__(self, itemaccount, taxaccount, insuranceaccount,
                 fixedcostaccount, shippingcostaccount):
        
        self.root = FOElement('HIPAY_MAPI_PaymentParams')
        
        self.itemaccount = itemaccount
        self.taxaccount = taxaccount
        self.insuranceaccount = insuranceaccount
        self.fixedcostaccount = fixedcostaccount
        self.shippingcostaccount = shippingcostaccount
        
        if (self.itemaccount):
            self.root = setTag(dict(itemAccount=self.itemaccount), self.root)

        if (self.taxaccount):
            self.root = setTag(dict(taxAccount=self.taxaccount), self.root)

        if (self.insuranceaccount):
            self.root = setTag(dict(insuranceAccount=self.insuranceaccount), self.root)

        if (self.fixedcostaccount):
            self.root = setTag(dict(fixedCostAccount=self.fixedcostaccount), self.root)

        if (self.shippingcostaccount):
            self.root = setTag(dict(shippingCostAccount=self.shippingcostaccount), self.root)
            

    def setBackgroundColor(self, bg_color):
        """Sets the background color of the payment interface (as hexadecimal
        #XXXXXX).  Default color (white) is advised as image transparency remains
        problematic on several web browsers.  Obsolete method, prefer to set css
        stylesheet on website form in “payment buttons” section."""
        # FIXME: Check if valid color code
        self.bg_color = bg_color
        if bg_color:
            self.root = setTag(dict(bg_color=bg_color), self.root)


    def setCaptureDay(self, captureday):
        """Defines the capture period.  The capture period corresponds to the
        time period, in days, between the authorization of the transaction and the
        transfer of the amount to the merchant’s account.  During the transaction
        authorization process, the amount paid by the customer is set aside and is
        therefore no longer available to the customer for other purchases.  The value of
        captureDay can be: HIPAY_MAPI_CAPTURE_IMMEDIATE: The capture is completed
        immediately.  HIPAY_MAPI_CAPTURE_MANUAL: The capture will be carried out by the
        merchant.  Value > 0: Number of days before capture.  The number of days before
        capture cannot exceed the value HIPAY_MAPI_CAPTURE_MAX_DAYS (generally 7 days,
        as defined by Hipay).  If manual capture is selected, the capture must occur
        before the HIPAY_MAPI_CAPTURE_MAX_DAYS period expires, or the transaction is
        cancelled and this amount is made available once again to the customer."""
        self.captureday = captureday
        if (captureday in ('HIPAY_MAPI_CAPTURE_IMMEDIATE',
                           'HIPAY_MAPI_CAPTURE_MANUAL')) or (str(captureday).isdigit() and 0 < int(captureday) < 7):
            self.root = setTag(dict(captureDay=captureday), self.root)            

        else:
            raise ValueError(_("""The value should be less than HIPAY_MAPI_CAPTURE_MAX_DAYS. You may also use 'HIPAY_MAPI_CAPTURE_IMMEDIATE', 'HIPAY_MAPI_CAPTURE_MANUAL' constants"""))
        

    def setCurrency(self, currency):
        """Defines the currency. This must be specified by its ISO-4217 code:
        EUR, USD, CAD, AUD, CHF, SEK, GBP.  The currency must be the same as the
        website’s account."""
        self.currency = currency
        if currency in ('EUR', 'USD', 'CAD', 'AUD', 'CHF', 'SEK', 'GBP'):
            self.root = setTag(dict(currency=currency), self.root)
        else:
            raise ValueError(_("""Unknown currency %s""" %currency))
        

    def setLocale(self, defaultlang):
        """Sets the default language of the interface (az_AZ = language_COUNTRY)
        available values : fr_FR, fr_BE, de_DE, en_GB, en_US, es_ES, nl_NL, nl_BE, pt_PT
        default language is on merchant account
        replace setDefaultLang which is obsolete"""
        self.defaultlang = defaultlang
        if defaultlang in ('fr_FR', 'fr_BE', 'de_DE', 'en_GB', 'en_US', 'es_ES', 'nl_NL', 'nl_BE', 'pt_PT'):
            self.root = setTag(dict(defaultLang=defaultlang), self.root)
        else:
            raise ValueError(_("""Unknown locale %s""" %defaultlang))
            

    def setEmailAck(self, email_ack):
        """Sets the email address for payment notification.  An email will be
        sent to this address after each payment, whether successful or not.  This
        address is generally set by merchant administrator."""
        self.email_ack = email_ack
        if EMAIL_RE.search(email_ack):
            self.root = setTag(dict(email_ack=email_ack), self.root)
        else:
            raise ValueError(_("""Invalid email address %s""" %email_ack))

    def setIdForMerchant(self, idformerchant):
        """Defines the identifier of this sale in the merchant’s system.
        This alphanumeric identifier is free"""
        self.idformerchant = idformerchant
        if idformerchant:
            self.root = setTag(dict(idForMerchant=idformerchant), self.root)        

    def setLogin(self, login, password):
        """The login is the ID of the hipay website merchant account receiving
        the payment, and the password is the « merchant password » set within your
        website.  For further information, please refer to the "payment buttons > Add a
        new site" of the main Hipay website.

        Please note: This is not the login and password used to connect to the Hipay
        site, but rather the login and password for connection to the gateway.  For more
        information, refer to the “Payment Interface” section on the Hipay website.
        """
        self.login = login
        self.password = password
        if login and password:
            self.root = setTag(dict(login=login, password=password), self.root)        
        

    def setLogoURL(self, logo_URL):
        """Sets the merchant’s logo URL, this logo will be displayed on your
        payment pages..  This logo, in GIF, PNG or JPG (JPEG) format must be accessible
        from the Internet via HTTPS protocol.  This logo must not exceed 100/100 pixels
        in size. """
        self.logo_URL = logo_URL
        if URL_RE.search(logo_URL):
            self.root = setTag(dict(logo_url=logo_URL), self.root)
        else:
            raise ValueError(_("""Invalid url %s""" %logo_URL))
        

    def setMedia(self, media):
        """Defines the payment interface type.  Currently, only the value “WEB"
        is allowed."""
        self.media = media
        if media in ('WEB',):
            self.root = setTag(dict(media=media), self.root)
        else:
            raise ValueError(_("""Invalid media %s""" %media))

    def setMerchantDatas(self, merchantdatas):
        """Sets the merchant’s data in the form of key-value dict.  These data
        elements will be returned in the XML [C] data feed to the merchant."""
        #FIXME: Multiple calls cleanup
        self.merchantdatas = merchantdatas
        if isinstance(merchantdatas, dict):
            element = FOElement('merchantDatas')
            element = setTag(merchantdatas, element)
            self.root.append(element)
            

    def setMerchantSiteId(self, merchantsiteId):
        """Defines the identifier of the merchant’s site.  This identifier
        corresponds to one of the sites listed in the “Merchant tool kit > Configure
        sites associated with the Hipay platform” section of the Hipay site."""
        self.merchantsiteId = merchantsiteId
        if merchantsiteId:
            self.root = setTag(dict(merchantSiteId=merchantsiteId), self.root)


    def setPaymentMethod(self, paymentmethod):
        """Defines whether the payment is single or recurring.  Possible values
        are: HIPAY_MAPI_METHOD_SIMPLE: Single payment.  HIPAY_MAPI_METHOD_MULTI
        : Recurring payment.  Note that you don't need to set this, the HiPay
        class will do the right thing depending on the called method Simple vs
        Multiple Payment"""
        self.paymentmethod = paymentmethod
        if paymentmethod in ('HIPAY_MAPI_METHOD_SIMPLE', 'HIPAY_MAPI_METHOD_MULTI', 0, 1):
            self.root = setTag(dict(paymentMethod=paymentmethod), self.root)
        else:
            raise ValueError(_("""Invalid payment method  %s""" %paymentmethod))
        

    def setRating(self, rating):
        """Defines the audience rating for the product, as it relates to this payment.
        The possible values are:
        ALL : For all ages.
        +12 : For ages 12 and over.
        +16 : For ages 16 and over.
        +18 : For ages 18 and over"""
        self.rating = rating
        if rating in ('ALL', '+12', '+16', '+18'):
            self.root = setTag(dict(rating=rating), self.root)
        else:
            raise ValueError(_("""Invalid rating  %s""" %rating))
        

    def setURLAck(self, URL_ack):
        """Sets the URL to be called to notify the merchant of the results of
        the operations carried out on the transaction (sending of the XML feed [C])."""
        self.URL_ack = URL_ack
        if URL_RE.search(URL_ack):
            self.root = setTag(dict(url_ack=URL_ack), self.root)
        else:
            raise ValueError(_("""Invalid url %s""" %URL_ack))
        

    def setURLCancel(self, URL_cancel):
        """Sets the URL where customers must be redirected to if the payment is
        cancelled."""
        self.URL_cancel = URL_cancel
        if URL_RE.search(URL_cancel):
            self.root = setTag(dict(url_cancel=URL_cancel), self.root)
        else:
            raise ValueError(_("""Invalid url %s""" %URL_cancel))
        

    def setURLNok(self, URL_nok):
        """Sets the URL where customers must be redirected to if the payment is
        not authorized."""
        self.URL_nok = URL_nok
        if URL_RE.search(URL_nok):
            self.root = setTag(dict(url_nok=URL_nok), self.root)
        else:
            raise ValueError(_("""Invalid url %s""" %URL_nok))
        

    def setURLOk(self, URL_ok):
        """Sets the URL where customers must be redirected to if the payment is
        authorized."""
        self.URL_ok = URL_ok
        if URL_RE.search(URL_ok):
            self.root = setTag(dict(url_ok=URL_ok), self.root)
        else:
            raise ValueError(_("""Invalid url %s""" %URL_ok))
        

    def setInformations(self, text):
        """to display free text under the shopping cart on payment pages."""
        self.text = text
        if text:
            self.root = setTag(dict(Informations=text), self.root)
        

    def setIssuerAccountLogin(self, hipay_account_login):
        """set email of a known hipay customer account to precheck login form on
        payment page"""
        self.hipay_account_login = hipay_account_login
        if EMAIL_RE.search(hipay_account_login):
            self.root = setTag(dict(emailClient=hipay_account_login), self.root)
        else:
            raise ValueError(_("""Invalid email address %s""" %hipay_account_login))
        

    def setShopId(self, id_shop):
        """ to replace default merchant description on payment pages. Get the
        shop Id on « payment buttons > shop section"""
        self.id_shop = id_shop
        if EMAIL_RE.search(id_shop):
            self.root = setTag(dict(shopId=id_shop), self.root)
        else:
            raise ValueError(_("""Invalid email address %s""" %id_shop))
        

    def check(self):
        """Verifies that the object is correctly initialized. This could be done
        through validation at https://payment.hipay.com/schema/mapi.xsd"""
        pass



class Tax(HiPayTree):
    def __init__(self, root):
        # root may be equal to tax or shippingTax or insuranceTax or fixedCostTax
        self.root = FOElement(root)

    def setTaxes(self, taxes):
        """Sets the value of the tax.  The value can be a fixed amount or a
        percentage.  In the latter case, percentage must have the value “true”"""
        #FIXME: This is not the right data type to use since it impose to the
        #developer to know the correct tag name which is none of his concern
        self.taxes = taxes # list of dict(taxName=k, taxVal=v, percentage=p)
        if taxes and all([k['percentage'] in ('true', 'false') and is_number(k['taxVal']) for k in taxes]):
            for taxe in taxes:
                element = FOElement('HIPAY_MAPI_Tax')
                element = setTag(taxe, element)
                self.root.append(element)
        else:
            raise ValueError(_("""taxVal and percentage should be numbers %s""" %taxes))
                

class Affiliate(HiPayTree):
    def __init__(self):
        self.root = FOElement('affiliate')

    def setValue(self, affiliates):
        """
        Sets the value of the affiliation which is either fixed amount or a
        percentage.  If it is a percentage, percentageTarget represents the target,
        that is, on which amount(s) the affiliation amount is based.

        percentageTarget is the addition of all or part of the following values:
        HIPAY_MAPI_TTARGET_TAX :
        The “tax amount” is included in the percentage calculation.
        HIPAY_MAPI_TTARGET_INSURANCE :
        The “insurance amount” (excluding taxes) is included in the percentage calculation.
        HIPAY_MAPI_TTARGET_FCOST :
        The “fixed costs” amount (excluding taxes) is included in the percentage calculation.
        HIPAY_MAPI_TTARGET_SHIPPING :
        The “shipping cost” amount (excluding taxes) is included in the percentage calculation.
        HIPAY_MAPI_TTARGET_ITEM :
        The “products amount” (excluding taxes) is included in the percentage calculation."""
        self.affiliates = affiliates # list of dict(customerId=k, accountId=v, percentageTarget=p)
        if affiliates and all(is_number(k['percentageTarget']) for k in affiliates):
            for aff in affiliates:
                element = FOElement('HIPAY_MAPI_Affiliate')
                element = setTag(aff, element)
                self.root.append(element)
        else:
            raise ValueError(_("""Invalid percentageTarget %s""" %affiliates))
            
            

class Product(HiPayTree):
    def __init__(self):
        self.root = FOElement('items')

    def setProducts(self, products): #list of dict(name,info, quantity,ref,
        #category, price, tax), tax is a Tax instance
        self.products = products
        if products and all([(isinstance(k['quantity'], int) or
                              str(int(k['quantity'])).isdigit()) and
                             (isinstance(k['price'], float) or
                              is_number(k['price'])) and
                             isinstance(k['tax'], Tax)] for k in products):
            for p in products:
                element = FOElement('HIPAY_MAPI_Product')
                element = setTag(p, element)
                self.root.append(element)
        else:
            raise ValueError(_("""Invalid products specifications, the quantity and the price should be numbers %s""" %products))
        

class Installement(HiPayTree):
    def __init__(self):
        self.root = FOElement('items')        

    def setInstallements(self, installements):
        self.installements = installements
        if installements and all([is_number(k['price']) and
                                  isinstance(k['tax'], Tax) and
                                  k['first'] in ('true', 'false') for k in installements]):
            for i in installements:
                element = FOElement('HIPAY_MAPI_Installment')
                element = setTag(i, element)
                self.root.append(element)
        else:
            raise ValueError(_("""Invalid installement specifications, the price should be numbers and first should be true or false %s""" %installements))
        
class Order(HiPayTree):
    def __init__(self):
        self.root = FOElement('order')
        
    def setOrders(self, orders):
        self.orders = orders
        if orders and all([is_number(k['shippingAmount']) and
                           is_number(k['insuranceAmount']) and
                           is_number(k['fixedCostAmount']) and
                           str(k['orderCategory']).isdigit() and
                           ('insuranceTax' not in orders or ('insuranceTax' in orders and isinstance(k['insuranceTax'], Tax))) and
                           ('fixedCostTax' not in orders or ('fixedCostTax' in orders and isinstance(k['fixedCostTax'], Tax))) and
                           ('affiliate' not in orders or ('affiliate' in orders and isinstance(k['affiliate'], Affiliate))) for k in orders]):
            for o in orders:
                element = FOElement('HIPAY_MAPI_Order')
                element = setTag(o, element)
                self.root.append(element)
        else:
            raise ValueError(_("""Invalid order specifications, shippingAmount, insuranceAmount, fixedCostAmount should numbers, orderCategory is an integer insuranceTax, fixedCostTax should be Tax instances and affiliate should be an Affiliate instance%s""" %orders))


class HiPay(HiPayTree):

    def __init__(self, params):
        self.params = params
        self.user_agent = "Hipay Python Mapi"

    def SimplePayment(self, orders, products):
        self.params.setPaymentMethod(0)
        self.root = FOElement('HIPAY_MAPI_SimplePayment')
        self.root.extend([self.params.asTree().getroot(), orders.asTree().getroot(), products.asTree().getroot()])
        
    def MultiplePayment(self, orders, installements):
        self.params.setPaymentMethod(1)
        self.root = FOElement('HIPAY_MAPI_MultiplePayment')
        self.root.extend([self.params.asTree().getroot(), orders.asTree().getroot(), installements.asTree().getroot()])
        
    def SendPayment(self, gw):
        self.mapi =  FOElement('mapi')
        self.mapi = setTag({'mapiversion':'1.0'}, self.mapi)
        m = hashlib.md5()
        m.update(ET.tostring(self.asTree().getroot()))
        self.mapi = setTag({'md5content':m.hexdigest()}, self.mapi)        
        self.mapi.append(self.root)
        xml = ET.tostring(ET.ElementTree(self.mapi).getroot(), encoding="utf-8")
        opener = urllib2.build_opener()
        opener.addheaders = [("Content-Type", "text/xml"),
                             ("Content-Length", str(len(xml))),
                             ("User-Agent", self.user_agent)]
        urllib2.install_opener(opener)
        
        request = urllib2.Request(gw,urlencode({'xml':xml}))
        response = opener.open(request)
        self.response = response
        return self.ProcessAnswer()
        
    
    def ProcessAnswer(self):
        try:
            response = self.response.read()
        except:
            raise ValueError(_("Empty answer"))
        tree = ET.fromstring(response)
        if tree.find('result/status').text == 'error':
            return dict(status='Error', message=tree.find("result/message").text)
            #raise ValueError(_("""Error: %s""" %(tree.find("result/message"),)))
        elif tree.find('result/status').text == 'accepted':
            return dict(status='Accepted',message=tree.find("result/url").text)
        else:
            return dict(status='Unknown', message=response)


def ParseAck(ack=None):
    if not ack:
        return None
    tree = ET.fromstring(ack)
    body = tree.find('result')
    m = hashlib.md5()
    m.update(ET.tostring(ET.ElementTree(body).getroot()))
        
    # If this is a subscription
    try:
        subscriptionId = tree.find('result/subscriptionId').text
    except:
        subscriptionId = None
            

                        
    if tree.find('result/merchantDatas') is not None:
        merchantDatas = dict([(i.tag, i.text) for i in tree.find('result/merchantDatas')])
    else:
        merchantDatas = None
    return {'operation':tree.find('result/operation').text,
            'status':tree.find('result/status').text,
            'date':tree.find('result/date').text,
            'time':tree.find('result/time').text,
            'transid':tree.find('result/transid').text,
            'origAmount':tree.find('result/origAmount').text,
            'origCurrency': tree.find('result/origCurrency').text,
            'idForMerchant': tree.find('result/idForMerchant').text,
            'emailClient': tree.find('result/emailClient').text,
            'merchantDatas':merchantDatas,
            'subscriptionId':subscriptionId,
            'refProduct': tree.find('result/refProduct0').text,
            'not_tempered_with': tree.find('md5content').text == m.hexdigest()
             }
