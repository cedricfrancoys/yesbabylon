Export IIS certificate to Linux certificate

### extract private key (might be encrypted)
openssl pkcs12 -in <filename>.pfx -nocerts -out domain.pem

### extract CERTIFICATE (x509)
openssl pkcs12 -in <filename>.pfx -clcerts -nokeys -out domain.crt

### generate RSA PRIVATE KEY
openssl rsa -in domain.pem -out domain.key

### generate CERTIFICATE CHAIN
* see the issuer certificate URL
  openssl x509 -in domain.crt -text | grep crt
* download issuer certificate
  wget {URL}
* convert der file to pem
  openssl x509 -inform der -in signer.der -out signer.pem