#!/usr/bin/env python3
# See https://docs.python.org/3.2/library/socket.html
# for a decscription of python socket and its parameters
import socket

from threading import Thread
from argparse import ArgumentParser

# for the server you may find the following python libraries useful:
import os #- check to see if a file exists in python -
# e.g., os.path.isfile, os.path.exists

import stat
#check if a file has others permissions set, os.stat

import sys #- enables you to get the argument vector (argv) from command line and use
# values passed in from the command line


#other defintions that will come in handy for getting data and
#constructing a response
BUFSIZE = 4096
CRLF = '\r\n'
#You mighte find it useful to define variables similiar to the one above
#for each kind of response message

#Outline for processing a request - indicated by the call to processreq below
#the outline below is for a GET request, though the others should be similar (but not the same)
#remember, you have an HTTP Message that you are parsing
#so, you want to parse the message to get the first word on the first line
#of the message (the HTTP command GET, HEAD, ????) if the HTTP command is not known you should respond with an error
#then get the  resource (file path and name) - python strip and split should help
#Next,  does the resource have a legal name (no % character) 
#			if false  - construct an error message for the response and return
#     if true - check to see if the resource exists
#				if false - construct an error message for the response and return
#				if true - check to see if the permissions on the resource for others are ok
#					if false - construct an error message for the response and resturn
#					if true - Success!!! 
#           open the resource (file)
#           read the resource into a buffer
#           create a response message by concatenating the OK message above with
#             the string you read in from the file
#           return the response

version = 'HTTP/1.1'
methods = ['GET','HEAD']
HTTPcode = {}
HTTPcode['200'] = version+' 200 OK'+CRLF
HTTPcode['301'] = version+' 301 Moved Permanently'+CRLF
HTTPcode['403'] = version+' 403 Forbidden'+CRLF
HTTPcode['404'] = version+' 404 Not Found'+CRLF
HTTPcode['405'] = version+' 405 Method Not Allowed'+CRLF+'Allow: '+str.join(', ',methods)+CRLF+CRLF
HTTPcode['406'] = version+' 406 Not Acceptable'+CRLF
MIMEtype = {
	'html':'text/html',
	'jpeg':'image/jpeg',
	'gif':'image/gif',
	'pdf':'application/pdf',
	'doc':'application/msword',
	'pptx':'application/vnd.openxmlformats-officedocument.presentationml.presentation'
}
orderedMIMEtype = ['html','jpeg','gif','pdf','doc','pptx']

def processHeaders(lines):
	headers = {}
	while lines[0] != '':
		(hName, ignore, hValue) = lines[0].partition(': ')
		hValue = hValue.rstrip()
		hValues = [v.strip() for v in hValue.split(',')]
		headers[hName] = hValues
		del lines[0]
	del lines[0]
	return (lines, headers)

def searchByTypeForFiletypesOfURL(URL):
	types = []
	URLtype = URL.rpartition('.')[0]
	if URLtype == '': #none specified
		for key in orderedMIMEtype:
			if os.path.exists(URL+'.'+key) and os.path.isfile(URL+'.'+key):
				types += [key]
	else:
		if os.path.exists(URL) and os.path.isfile(URL):
			types += [URLtype]
	return types

#deprecated
# def searchByDirForFiletypesOfURL(URL):
# 	types = []
# 	URLtype = URL.rpartition('.')[2]
# 	(name,ignore,ldir) = URL.rpartition('/')
# 	name = name.rpartition('.')[2]
# 	# for each (actual) file in ldir
# 	for item in os.listdir(ldir):
# 		if os.path.isfile(ldir+item):
# 			part = item.partition('.')
# 			if part[0] == name and (URLtype == '' or URLtype == part[2]):
# 				types += [part[2]]#store type
# 	return types

def processResponse(file, header, method):
	if method == 'GET': 
		f = open(file,'r')
		result = header + CRLF
		for line in f:
			result += line.rstrip() + CRLF
		return result
	elif method == 'HEAD':
		return header+CRLF+CRLF
	else:
		return HTTPcode['405']#send HTTP 405

def processRequest(requestMsg):
	lines = requestMsg.split(CRLF)
	requestLine = lines[0] #assume the requestLine exists/worked
	del lines[0]
	(method, URL, version) = requestLine.split(' ')
	URLtype = URL.rpartition('.')[0]
	if URL[0] == '/':
		URL = '.' + URL
	version = version.rstrip()

	#URL(.*) doesn't exist
	existingTypes = searchByTypeForFiletypesOfURL(URL)
	if existingTypes == []:
		return processResponse('./404.html', HTTPcode['404'], method) #send HTTP 404
	if URLtype == '':#if type was not specified
		URL = URL + '.' + existingTypes[0]#specify it

	#URL(.*) not readable by "others"
	if stat.S_IMODE(os.stat(URL).st_mode) & stat.S_IROTH == 0:
		return processResponse('./403.html', HTTPcode['403'], method)#send HTTP 403

	(lines, headers) = processHeaders(lines)
	if 'accept' not in headers:
		headers['accept'] = orderedMIMEtype
	if existingTypes[0] != URLtype:
		flag = False
		for t in existingTypes:
			if t in headers['accept']:
				flag = True
		if not flag:
			return HTTPcode['406']\
			+"Content-type: "+str.join(', ',[MIMEtype[t] for t in existingTypes])+CRLF+CRLF

#determine redirect?

	return processResponse(URL, HTTPcode['200'], method)




def client_talk(client_sock, client_addr):
	print('talking to {}'.format(client_addr))
	data = client_sock.recv(BUFSIZE)
	# note, here is where you decode the data and process the request
	req = data.decode('utf-8')
	# then, you'll need a routine to process the data, and formulate a response
	response = processRequest(req) 
	#once have the response, you send it
	client_sock.send(bytes(response, 'utf-8'))

	# clean up
	client_sock.shutdown(1)
	client_sock.close()
	try:
		print('connection closed.')
	except:
		pass		

class EchoServer:
	def __init__(self, host, port):
		print('listening on port {}'.format(port))
		self.host = host
		self.port = port

		self.setup_socket()

		self.accept()
		self.sock.shutdown()
		self.sock.close()
		print("server done.")

	def setup_socket(self):
		self.sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
		self.sock.bind((self.host, self.port))
		self.sock.listen(128)

	def accept(self):
		while True:
			(client, address) = self.sock.accept()
			th = Thread(target=client_talk, args=(client, address))
			th.run()

def parse_args():
	parser = ArgumentParser()
	parser.add_argument('--host', type=str, default='localhost',
											help='specify a host to operate on (default: localhost)')
	parser.add_argument('-p', '--port', type=int, default=9001,
											help='specify a port to operate on (default: 9001)')
	args = parser.parse_args()
	return (args.host, args.port)


if __name__ == '__main__':
	(host, port) = parse_args()
	EchoServer(host, port)

