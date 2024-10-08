FROM golang:1.17-buster as base

RUN apt-get update && apt-get install -y protobuf-compiler cmake
RUN mkdir /protobuf
WORKDIR /protobuf
RUN cd /protobuf && git clone -b v1.54.2 --depth 1 https://github.com/grpc/grpc && cd grpc && git submodule update --init && mkdir -p cmake/build && cd cmake/build && cmake ../.. && make protoc grpc_php_plugin

RUN curl -L https://github.com/roadrunner-server/roadrunner/releases/download/v2023.1.1/protoc-gen-php-grpc-2023.1.1-linux-amd64.tar.gz | tar -xz
RUN mv protoc-gen-php-grpc-2023.1.1-linux-amd64/protoc-gen-php-grpc /usr/local/go/bin/protoc-gen-php-grpc

RUN git clone --branch v0.1.1 https://github.com/solo-io/protoc-gen-openapi.git /tmp-openapi && \
    cd /tmp-openapi && \
    go build

FROM golang:1.17-buster

RUN apt-get update && apt-get install -y protobuf-compiler git make cmake bash

COPY --from=base /protobuf/grpc/cmake/build/grpc_php_plugin /usr/local/go/bin/grpc_php_plugin

COPY --from=base /usr/local/go/bin/protoc-gen-php-grpc /usr/local/go/bin/protoc-gen-php-grpc
COPY --from=base /tmp-openapi/protoc-gen-openapi /usr/local/go/bin/protoc-gen-openapi

RUN apt-get update && apt-get install -y libprotobuf-dev
RUN export GO111MODULE=on && \
    go install github.com/golang/protobuf/protoc-gen-go@v1.5.3 && \
    go install google.golang.org/protobuf/cmd/protoc-gen-go@v1.32.0 && \
    go install google.golang.org/grpc/cmd/protoc-gen-go-grpc@v1.3.0

WORKDIR /var/www
