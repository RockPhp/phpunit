#!/bin/bash

prj_src_folder=$(dirname $0)
file_path="${@: -1}"
base_name=$(basename "${file_path}")
src_folder=$(dirname "${file_path}")

docker run --rm --net=host \
 -v "${prj_src_folder}":"${prj_src_folder}" \
 -w "${src_folder}" \
 php:5.1 "${prj_src_folder}/vendor/bin/phpunit" --printer Eclipse_PHPUnitLogger "${base_name}"
