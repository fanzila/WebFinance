#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 17 17:44:19 2011"


from django.test import TestCase
from django.core.urlresolvers import reverse
from django.utils.translation import ugettext_lazy as _
import hipay
import hashlib
import xml.etree.ElementTree as ET
class HiPayTest(TestCase):
    def setUp(self):
        # We need a ticket and an account for test to pass before we use
        # selenium and friends
        # Hipay credentials
        self.login = '9f5b8ba9c9feca32055f0b5a9bcffb74'
        self.password = '7a745b3328536de84831d5f55f56d74d'

    def test_params(self):
        s = hipay.PaymentParams("123", "124", "125", "126", "127")
        s.setBackgroundColor('#234567')
        s.setCaptureDay('6')
        s.setCurrency('EUR')
        s.setLocale('fr_FR')
        s.setEmailAck('test@example.org')
        s.setLogin('user', 'password')
        s.setMerchantDatas({'alpha':23, 'beta':34})
        self.assertEqual(hashlib.sha224(ET.tostring(s.asTree().getroot())).hexdigest(),
                         '55d3ade8f0f4708083011834c2163cbd78011051f3e6efbf76b6ee87')


    def test_taxes(self):
        mta = hipay.Tax('tax')
        mestax=[dict(taxName='TVA 19.6', taxVal='19.6', percentage='true'), dict(taxName='TVA 5.5', taxVal='5.5', percentage='true')]
        mta.setTaxes(mestax)
        self.assertEqual(hashlib.sha224(ET.tostring(mta.asTree().getroot())).hexdigest(),
                         '7dab36f4b0338e2511f7778b82d5963d2b24829ae3c67e8f3a909709')

        #### Various taxes
        checksums={'shippingTax':'9e66ef9ccc2d44a66af43a9c7e6b47d923988daffb96be0113cc1842',
                   'insuranceTax':'356b55f79c38c418fc2f67093b7f78cd7e62b1840c526ddc19595c5f',
                   'fixedCostTax':'ace001c581f1ca253017e6f75443453f0bc968fb2e6fbc51b57c1c61'
                   }
        taxes = dict()
        for root in 'shippingTax', 'insuranceTax', 'fixedCostTax':
            taxes[root] = hipay.Tax(root)
            mestax=[dict(taxName='TVA', taxVal='19.6', percentage='true')]
            taxes[root].setTaxes(mestax)
            self.assertEqual(hashlib.sha224(ET.tostring(taxes[root].asTree().getroot())).hexdigest(),
                             checksums[root])

    def test_affiliatess(self):
        af = hipay.Affiliate()
        affiliates = [dict(customerId='123', accountId='valeur',percentageTarget='12.6')]
        af.setValue(affiliates)
        self.assertEqual(hashlib.sha224(ET.tostring(af.asTree().getroot())).hexdigest(),
                             'cddc5fc48d45e057c67d7c360abe6dba49fdc8d79a2436c20376a1e9')


    def test_products(self):
        pr = hipay.Product()
        mta = hipay.Tax('tax')
        mestax=[dict(taxName='TVA 19.6', taxVal='19.6', percentage='true'), dict(taxName='TVA 5.5', taxVal='5.5', percentage='true')]
        mta.setTaxes(mestax)
        
        products = [{'name':'The Fall of  Hyperion','info':u'Simmons, Dan – ISBN 0575076380', 'quantity':'10', 'ref':'10', 'category':'91', 'price':'120', 'tax':mta},
                {'name':'The Fall of  Hyperion','info':u'Simmons, Dan – ISBN 0575076380', 'quantity':'10', 'ref':'10', 'category':'91', 'price':'120', 'tax':mta}]
        pr.setProducts(products)
        self.assertEqual(hashlib.sha224(ET.tostring(pr.asTree().getroot())).hexdigest(),
                         'adb1fe832c8fd782f7c00803dcbafafb083d929e457aa530256ca5c2')


    def test_installement(self):
        inst = hipay.Installement()
        mta = hipay.Tax('tax')
        mestax=[dict(taxName='TVA 19.6', taxVal='19.6', percentage='true'), dict(taxName='TVA 5.5', taxVal='5.5', percentage='true')]
        mta.setTaxes(mestax)        
        data = [{'price':100, 'first':'true','paymentDelay':'1D', 'tax':mta},{'price':100, 'first':'false','paymentDelay':'1M', 'tax':mta}]
        inst.setInstallements(data)
        self.assertEqual(hashlib.sha224(ET.tostring(inst.asTree().getroot())).hexdigest(),
                         '12c360c2946a1570cd603aff58c093965785a3a7696f75a05c6c18e2')


    def test_orders(self):
        order = hipay.Order()
        af = hipay.Affiliate()
        affiliates = [dict(customerId='123', accountId='valeur',percentageTarget='12.6')]
        af.setValue(affiliates)        
        taxes = dict()
        for root in 'shippingTax', 'insuranceTax', 'fixedCostTax':
            taxes[root] = hipay.Tax(root)
            mestax=[dict(taxName='TVA', taxVal='19.6', percentage='true')]
            taxes[root].setTaxes(mestax)
        
        data = [{'shippingAmount':1.50, 'insuranceAmount':2.00, 'fixedCostAmount':2.25, 'fixedCostTax':taxes['fixedCostTax'], 'insuranceTax': taxes['insuranceTax'], 'shippingTax':taxes['shippingTax'], 'orderTitle':'Mon ordre', 'orderiInfo':'Box', 'orderCategory':91, 'affiliate':af},
            {'shippingAmount':1.50, 'insuranceAmount':2.00, 'fixedCostAmount':2.25, 'fixedCostTax':taxes['fixedCostTax'], 'insuranceTax': taxes['insuranceTax'], 'shippingTax':taxes['shippingTax'], 'orderTitle':'Mon ordre 2', 'orderiInfo':'Box 2', 'orderCategory':91, 'affiliate':af}]
        order.setOrders(data)
        self.assertEqual(hashlib.sha224(ET.tostring(order.asTree().getroot())).hexdigest(),
                         'e393d8a10a0fa6d66be1ff71ea5d02a03fd0e94219dd5fa985ed22c2')

    def test_simplepayment(self):
        s = hipay.PaymentParams("123", "124", "125", "126", "127")
        s.setBackgroundColor('#234567')
        s.setCaptureDay('6')
        s.setCurrency('EUR')
        s.setLocale('fr_FR')
        s.setEmailAck('test@example.org')
        s.setLogin('user', 'password')
        s.setMerchantDatas({'alpha':23, 'beta':34})
        
        pr = hipay.Product()
        mta = hipay.Tax('tax')
        mestax=[dict(taxName='TVA 19.6', taxVal='19.6', percentage='true'), dict(taxName='TVA 5.5', taxVal='5.5', percentage='true')]
        mta.setTaxes(mestax)
        
        products = [{'name':'The Fall of  Hyperion','info':u'Simmons, Dan – ISBN 0575076380', 'quantity':'10', 'ref':'10', 'category':'91', 'price':'120', 'tax':mta},
                {'name':'The Fall of  Hyperion','info':u'Simmons, Dan – ISBN 0575076380', 'quantity':'10', 'ref':'10', 'category':'91', 'price':'120', 'tax':mta}]
        pr.setProducts(products)
        
        order = hipay.Order()
        af = hipay.Affiliate()
        affiliates = [dict(customerId='123', accountId='valeur',percentageTarget='12.6')]
        af.setValue(affiliates)        
        taxes = dict()
        for root in 'shippingTax', 'insuranceTax', 'fixedCostTax':
            taxes[root] = hipay.Tax(root)
            mestax=[dict(taxName='TVA', taxVal='19.6', percentage='true')]
            taxes[root].setTaxes(mestax)
        
        data = [{'shippingAmount':1.50, 'insuranceAmount':2.00, 'fixedCostAmount':2.25, 'fixedCostTax':taxes['fixedCostTax'], 'insuranceTax': taxes['insuranceTax'], 'shippingTax':taxes['shippingTax'], 'orderTitle':'Mon ordre', 'orderiInfo':'Box', 'orderCategory':91, 'affiliate':af},
            {'shippingAmount':1.50, 'insuranceAmount':2.00, 'fixedCostAmount':2.25, 'fixedCostTax':taxes['fixedCostTax'], 'insuranceTax': taxes['insuranceTax'], 'shippingTax':taxes['shippingTax'], 'orderTitle':'Mon ordre 2', 'orderiInfo':'Box 2', 'orderCategory':91, 'affiliate':af}]
        order.setOrders(data)
        pay = hipay.HiPay(s)        
        pay.SimplePayment(order, pr)
        self.assertEqual(hashlib.sha224(ET.tostring(pay.asTree().getroot())).hexdigest(),
                         'cc5fc0379eda66ada5f335379e4faca03bc5cc3d1f4a1d167c486fc0')


    def test_multiplepayment(self):
        pass
                
