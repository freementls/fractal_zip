# Standalone: cmake -S . -B build && cmake --build build
# Squash tree: add_subdirectory(this dir as plugins/fzcodec) — SQUASH_VERSION_API is set.
cmake_minimum_required(VERSION 3.16)

if (DEFINED SQUASH_VERSION_API)
	add_subdirectory(plugins/squash)
	return()
endif()

project(fzcodec C CXX)
set(CMAKE_C_STANDARD 11)
set(CMAKE_C_STANDARD_REQUIRED ON)
set(CMAKE_CXX_STANDARD 17)
set(CMAKE_POSITION_INDEPENDENT_CODE ON)

find_package(Threads REQUIRED)
find_package(ZLIB REQUIRED)
find_package(PkgConfig REQUIRED)
pkg_check_modules(ZSTD REQUIRED libzstd)
pkg_check_modules(BROTLIENC REQUIRED libbrotlienc)
pkg_check_modules(BROTLIDEC REQUIRED libbrotlidec)

set(FZCODEC_HINT_ROOT "" CACHE PATH "Optional extra search root for lepton/bsc")
if (FZCODEC_HINT_ROOT STREQUAL "" AND EXISTS "${CMAKE_CURRENT_SOURCE_DIR}/../../tools/jpeg/lepton")
	set(FZCODEC_HINT_ROOT "${CMAKE_CURRENT_SOURCE_DIR}/../..")
endif()

add_library(fzcodec STATIC
	src/fzcodec.c
	src/fz_sniff.c
	src/fz_tools.c
	src/fz_zpaq.cpp
	third_party/libzpaq/libzpaq.cpp)
target_compile_definitions(fzcodec PRIVATE unix)
target_include_directories(fzcodec
	PUBLIC
		$<BUILD_INTERFACE:${CMAKE_CURRENT_SOURCE_DIR}/include>
		$<INSTALL_INTERFACE:include>
	PRIVATE
		src
		third_party/libzpaq
		${ZSTD_INCLUDE_DIRS}
		${BROTLIENC_INCLUDE_DIRS}
		${BROTLIDEC_INCLUDE_DIRS})
target_link_libraries(fzcodec
	PUBLIC
		${ZSTD_LIBRARIES}
		${BROTLIENC_LIBRARIES}
		${BROTLIDEC_LIBRARIES}
		ZLIB::ZLIB
		Threads::Threads)
if (NOT FZCODEC_HINT_ROOT STREQUAL "")
	target_compile_definitions(fzcodec PRIVATE "FZCODEC_HINT_ROOT=\"${FZCODEC_HINT_ROOT}\"")
endif()

add_executable(fzcodec-cli src/fz_cli.c)
set_target_properties(fzcodec-cli PROPERTIES OUTPUT_NAME fzcodec)
target_link_libraries(fzcodec-cli PRIVATE fzcodec)

add_executable(test_fzcodec tests/test_fzcodec.c)
target_link_libraries(test_fzcodec PRIVATE fzcodec)
enable_testing()
add_test(NAME test_fzcodec COMMAND test_fzcodec)
set_tests_properties(test_fzcodec PROPERTIES ENVIRONMENT FZCODEC_DISABLE_PAQ=1)

find_package(PkgConfig)
pkg_check_modules(SQUASH squash-0.8)
if (SQUASH_FOUND)
	add_subdirectory(plugins/squash)
endif()

include(GNUInstallDirs)
install(TARGETS fzcodec fzcodec-cli
	ARCHIVE DESTINATION ${CMAKE_INSTALL_LIBDIR}
	RUNTIME DESTINATION ${CMAKE_INSTALL_BINDIR})
install(FILES include/fzcodec.h DESTINATION ${CMAKE_INSTALL_INCLUDEDIR})
configure_file(cmake/fzcodec.pc.in fzcodec.pc @ONLY)
install(FILES ${CMAKE_CURRENT_BINARY_DIR}/fzcodec.pc
	DESTINATION ${CMAKE_INSTALL_LIBDIR}/pkgconfig)
