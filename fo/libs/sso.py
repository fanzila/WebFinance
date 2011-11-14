#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Mon Nov 14 11:33:29 2011"


from elementtree.ElementTree import Element, SubElement, QName, tostring, parse, ElementTree
import base64, urlparse, urllib2, StringIO
from SOAPpy import parseSOAPRPC
from SOAPpy.Types import faultType
from datetime import datetime

CYBSSO_LOGIN = "ousmane"
CYBSSO_PASSWORD = "LqayCNP126KV41cq8FI59hS5HBIPfVEs2EHdsuD8SNEabVulofP5ThTg6xLwvnHg"
credentials = dict(username=CYBSSO_LOGIN, password=CYBSSO_PASSWORD)
CYBSSO_URL = 'http://cybsso-dev.isvtec.com/api/'
#url = "http://browserspy.dk/password-ok.php"
base64string = base64.encodestring('%s:%s' % (CYBSSO_LOGIN, CYBSSO_PASSWORD))[:-1]

# namespaces (SOAP 1.1)
NS_SOAP_ENV = "{http://schemas.xmlsoap.org/soap/envelope/}"
NS_XSI = "{http://www.w3.org/1999/XMLSchema-instance}"
NS_XSD = "{http://www.w3.org/1999/XMLSchema}"

class WFHTTPClient:

    user_agent = "WEBFINACE-FO HTTPClient.py"

    def __init__(self, url, username=None, password=None):
        self.url = url
        self.username = username
        self.password = password

        scheme, host, path, params, query, fragment = urlparse.urlparse(url)
        if scheme != "http" and scheme != "https":
            raise ValueError("unsupported scheme (%s)" % scheme)

        self.host = host
        self.scheme = scheme
        

    def do_request(self, body,
                   path=None,
                   method="POST",
                   content_type="text/xml",
                   extra_headers=None,
                   parser=None):


        if isinstance(body, ElementTree):
            # serialize element tree
            afile = StringIO.StringIO()
            body.write(afile)
            body = afile.getvalue()


        passman = urllib2.HTTPPasswordMgrWithDefaultRealm()
        passman.add_password(None, self.host, self.username, self.password)
        authhandler = urllib2.HTTPBasicAuthHandler(passman)
        opener = urllib2.build_opener(authhandler)
        opener.addheaders = [("Content-Type", content_type),
                             ("Content-Length", str(len(body))),
                             ("User-Agent", self.user_agent),
                             ("Host", self.host)] + extra_headers

        urllib2.install_opener(opener)
        request = urllib2.Request(self.url,body)
        for k,v in opener.addheaders:
            request.add_header(k,v)

        try:
            h = opener.open(request)
        except Exception, e:
            #FIXME: Check for SOAPFault=500 even if the parser will get the
            #right instance, i.e e will be a faultType 
            h = e

        # Fix missing ns that break the parser
        afile = StringIO.StringIO()
        content = h.read()
        #Patch to avoid empty ns
        content = content.replace('xmlns:ns1=""','xmlns:ns1="http://xml.apache.org/xml-soap"')
        afile.write(content)
        afile.flush()
        h = afile
        if parser:
#            import pdb;pdb.set_trace()
            # FIXME: This is ugly, figure out why it can't read a StringIO
            return parser(content)

        # Fallback to ET parser
        return parse(h)

def SoapRequest(method):
    # create a SOAP request element
    request = Element(method)
    request.set(
        NS_SOAP_ENV + "encodingStyle",
        "http://schemas.xmlsoap.org/soap/encoding/"
        )
    return request

def SoapElement(parent, name, type=None, text=None):
    # add a typed SOAP element to a request structure
    elem = SubElement(parent, name)
    if type:
        if not isinstance(type, QName):
            type = QName("http://www.w3.org/1999/XMLSchema", type)
        elem.set(NS_XSI + "type", type)
    elem.text = text
    return elem


class SoapService:
    def __init__(self, url=None):
        self.__client = WFHTTPClient(url,CYBSSO_LOGIN,CYBSSO_PASSWORD)
    def call(self, action, request):
        # build SOAP envelope
        envelope = Element(NS_SOAP_ENV + "Envelope")
        body = SubElement(envelope, NS_SOAP_ENV + "Body")
        body.append(request)

        response = self.__client.do_request(
            tostring(envelope),
            extra_headers=[("SOAPAction", action)],
            parser =  parseSOAPRPC,
            )
        return response

class CYBSSOService(SoapService):
    """Implementation of public members of CYBSSO http://cybtools.isvtec.com/cybsso/classCybSSO.html"""
    def UserGetInfo(self, email):
        action = "urn:xmethods-delayed-quotes#UserGetInfo"
        request = SoapRequest("{urn:xmethods-delayed-quotes}UserGetInfo")
        SoapElement(request, "email", "string", email)
        response = self.call(action, request)
        if isinstance(response, faultType):
            return {'faultcode':response.faultcode, 'faultstring':response.faultstring, 'message':response.message}
        return dict((i['key'], i['value']) for i in response['return'][0])

    def TicketCheck(self, ticket, email):
        # The ticket and the email are got from GET once returned from the sso
        # login page with the return_url
        action = "urn:xmethods-delayed-quotes#TicketCheck"
        request = SoapRequest("{urn:xmethods-delayed-quotes}TicketCheck")
        SoapElement(request, "ticket", "string", ticket)
        SoapElement(request, "email", "string", email)        
        response = self.call(action, request)
        if isinstance(response, faultType):
            return {'faultcode':response.faultcode, 'faultstring':response.faultstring, 'message':response.message} 
        return datetime.fromtimestamp(float(response['return']))


if __name__ == '__main__':
    cybsso_ticket='0bb22a765b16355b504f88508d0140e5994f806760eb2ae5e4a8283764508577d427b328fe273a47'
    cybsso_email='ousmane@wilane.org'
    cybsso = CYBSSOService(CYBSSO_URL)
    print cybsso.UserGetInfo('ousmane@wilane.org')
    print cybsso.UserGetInfo('cyril@bouthors.org')
    print cybsso.TicketCheck(cybsso_ticket, cybsso_email)
    print cybsso.TicketCheck('%s123'%cybsso_ticket, cybsso_email)    
