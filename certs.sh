#!/bin/bash

# Create certificates directory
mkdir -p certs

# Generate CA private key
openssl genrsa -out certs/ca-key.pem 4096

# Generate CA certificate
openssl req -new -x509 -sha256 -days 3650 -key certs/ca-key.pem -out certs/ca.pem -subj "/C=US/ST=CA/L=San Francisco/O=MyOrg/OU=IT/CN=ca"

# Generate OpenSearch private key
openssl genrsa -out certs/opensearch-key.pem 4096

# Generate OpenSearch certificate signing request
openssl req -new -key certs/opensearch-key.pem -out certs/opensearch.csr -subj "/C=US/ST=CA/L=San Francisco/O=MyOrg/OU=IT/CN=opensearch"

# Create extensions file for OpenSearch certificate
cat > certs/opensearch.ext << EOF
authorityKeyIdentifier=keyid,issuer
basicConstraints=CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
subjectAltName = @alt_names

[alt_names]
DNS.1 = opensearch
DNS.2 = localhost
IP.1 = 127.0.0.1
EOF

# Generate OpenSearch certificate
openssl x509 -req -in certs/opensearch.csr -CA certs/ca.pem -CAkey certs/ca-key.pem -CAcreateserial -out certs/opensearch.pem -days 365 -sha256 -extfile certs/opensearch.ext

# Generate admin private key (for client authentication)
openssl genrsa -out certs/admin-key.pem 4096

# Generate admin certificate signing request
openssl req -new -key certs/admin-key.pem -out certs/admin.csr -subj "/C=US/ST=CA/L=San Francisco/O=MyOrg/OU=IT/CN=admin"

# Generate admin certificate
openssl x509 -req -in certs/admin.csr -CA certs/ca.pem -CAkey certs/ca-key.pem -CAcreateserial -out certs/admin.pem -days 365 -sha256

# Set proper permissions
chmod 600 certs/*-key.pem
chmod 644 certs/*.pem

echo "SSL certificates generated successfully!"
echo "Files created:"
echo "- certs/ca.pem (Certificate Authority)"
echo "- certs/ca-key.pem (CA Private Key)"
echo "- certs/opensearch.pem (OpenSearch Certificate)"
echo "- certs/opensearch-key.pem (OpenSearch Private Key)"
echo "- certs/admin.pem (Admin Certificate)"
echo "- certs/admin-key.pem (Admin Private Key)"