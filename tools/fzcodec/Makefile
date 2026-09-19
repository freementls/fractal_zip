# fzcodec — standalone C library + CLI + optional Squash plugin
CC      ?= gcc
CFLAGS  ?= -O2 -g
CFLAGS  += -std=c11 -Wall -Wextra -Werror -fPIC -pthread
HINT    := $(abspath ../..)
VENDOR  := third_party/libzpaq
CPPFLAGS += -Iinclude -Isrc -I$(VENDOR) -DFZCODEC_HINT_ROOT=\"$(HINT)\"
LDFLAGS ?=
CXX     ?= g++
CXXFLAGS ?= -O2 -g
CXXFLAGS += -std=c++17 -Wall -Wextra -fPIC -pthread
LIBS    := -lzstd -lbrotlienc -lbrotlidec -lz -lpthread -lstdc++

PREFIX  ?= /usr/local
DESTDIR ?=

BUILD   := build
LIB     := $(BUILD)/libfzcodec.a
CLI     := $(BUILD)/fzcodec
TESTBIN := $(BUILD)/test_fzcodec

LIB_OBJS := $(BUILD)/fz_sniff.o $(BUILD)/fz_tools.o $(BUILD)/fzcodec.o $(BUILD)/fz_zpaq.o $(BUILD)/libzpaq.o
CLI_OBJS := $(BUILD)/fz_cli.o

export PKG_CONFIG_PATH := $(CURDIR)/.deps/squash-prefix/lib/pkgconfig:$(PKG_CONFIG_PATH)
SQUASH_PC := $(shell pkg-config --exists squash-0.8 && echo squash-0.8 || true)
PLUGIN    := $(BUILD)/libsquash0.8-plugin-fzcodec.so
PLUGINDIR := $(BUILD)/squash-plugins/fzcodec
PLUGINTEST:= $(BUILD)/test_squash_plugin

.PHONY: all clean install install-plugin test test-corpus-smoke test-corpus-ultra-smoke test-corpus-medium test-corpus-large test-corpus-large-compare test-corpus test-jpeg plugin test-plugin test-plugin-files

all: $(LIB) $(CLI) $(TESTBIN)

$(BUILD):
	mkdir -p $(BUILD)

$(BUILD)/%.o: src/%.c include/fzcodec.h src/fz_internal.h | $(BUILD)
	$(CC) $(CPPFLAGS) $(CFLAGS) -c $< -o $@

$(BUILD)/fz_zpaq.o: src/fz_zpaq.cpp include/fzcodec.h src/fz_internal.h | $(BUILD)
	$(CXX) $(CPPFLAGS) $(CXXFLAGS) -Dunix -c $< -o $@

$(BUILD)/libzpaq.o: $(VENDOR)/libzpaq.cpp $(VENDOR)/libzpaq.h | $(BUILD)
	$(CXX) $(CPPFLAGS) $(CXXFLAGS) -Dunix -c $< -o $@

$(LIB): $(LIB_OBJS)
	$(AR) rcs $@ $^

$(CLI): $(CLI_OBJS) $(LIB)
	$(CC) $(CFLAGS) -o $@ $^ $(LDFLAGS) $(LIBS)

$(TESTBIN): tests/test_fzcodec.c $(LIB)
	$(CC) $(CPPFLAGS) $(CFLAGS) -o $@ $< $(LIB) $(LDFLAGS) $(LIBS)

test: $(TESTBIN)
	FZCODEC_DISABLE_PAQ=1 $(TESTBIN)

# Real JPEG transcode + roundtrip (content class, not a corpus id).
test-jpeg: $(CLI)
	@jpg="$(HINT)/test_files111/fireworks.jpeg"; \
	if [ ! -f "$$jpg" ]; then echo "skip test-jpeg: no fixture"; exit 0; fi; \
	tmp=$$(mktemp -d); \
	$(CLI) compress --lifestyle "$$jpg" "$$tmp/a.fzc"; \
	$(CLI) decompress "$$tmp/a.fzc" "$$tmp/out.jpg"; \
	cmp "$$jpg" "$$tmp/out.jpg"; \
	echo "ok jpeg lepton/lifestyle roundtrip $$(wc -c < $$tmp/a.fzc) bytes"; \
	rm -rf "$$tmp"

# Small Squash-mirror files only (paq allowed). Does not name datasets in the library.
test-corpus-smoke: $(CLI)
	env -u FZCODEC_DISABLE_PAQ php tests/test_corpus.php --lifestyle --max-bytes=40000

# Same slice, bytes-first preset.
test-corpus-ultra-smoke: $(CLI)
	env -u FZCODEC_DISABLE_PAQ php tests/test_corpus.php --ultra --max-bytes=40000

# Large files: BSC / zpaq / lepton. Paq is left off so this stays a tool
# smoke; lifestyle still runs paq8px on textlike/high-print up to 8 MiB.
# Self-describing zpaq wrap can sit a few KiB over squash's raw zpaq/5.
test-corpus-large: $(CLI)
	FZCODEC_DISABLE_PAQ=1 php tests/test_corpus.php --lifestyle --min-bytes=2000000 --roundtrip-only

# Same files vs published B-best (report only; wrap near-ties are expected).
test-corpus-large-compare: $(CLI)
	FZCODEC_DISABLE_PAQ=1 php tests/test_corpus.php --lifestyle --min-bytes=2000000 --report-only

# Medium singles (paq8px on): structured tables, prose, PDF streams.
test-corpus-medium: $(CLI)
	env -u FZCODEC_DISABLE_PAQ php tests/test_corpus.php --lifestyle --min-bytes=40001 --max-bytes=1100000

# Full 28-file lifestyle sweep vs published B-best.
test-corpus: $(CLI)
	env -u FZCODEC_DISABLE_PAQ php tests/test_corpus.php --lifestyle

plugin:
ifneq ($(SQUASH_PC),)
	$(CC) $(CPPFLAGS) $(CFLAGS) -shared -o $(PLUGIN) \
		plugins/squash/squash-fzcodec.c $(LIB) \
		$(shell pkg-config --cflags --libs $(SQUASH_PC)) \
		-Iinclude $(LDFLAGS) $(LIBS)
	mkdir -p $(PLUGINDIR)
	cp -f $(PLUGIN) $(PLUGINDIR)/
	cp -f plugins/squash/squash.ini $(PLUGINDIR)/squash.ini
	@echo "plugin: $(PLUGINDIR)"
else
	@echo "squash-0.8.pc not found; plugin sources are in plugins/squash/"
	@echo "Install libsquash-dev, or run scripts/setup_local_squash.sh"
endif

$(PLUGINTEST): tests/test_squash_plugin.c $(LIB) plugin
	$(CC) $(CPPFLAGS) $(CFLAGS) -o $@ tests/test_squash_plugin.c \
		$(shell pkg-config --cflags --libs $(SQUASH_PC)) \
		-Iinclude $(LIB) $(LDFLAGS) $(LIBS)

test-plugin: $(PLUGINTEST)
	FZCODEC_DISABLE_PAQ=1 \
		SQUASH_PLUGINS=$(abspath $(BUILD)/squash-plugins) \
		LD_LIBRARY_PATH=$(CURDIR)/.deps/squash-prefix/lib:$$LD_LIBRARY_PATH \
		$(PLUGINTEST)

# Real files through the .so (JPEG/lepton, small text+paq, large text/bsc).
test-plugin-files: $(PLUGINTEST)
	@export SQUASH_PLUGINS=$(abspath $(BUILD)/squash-plugins); \
	export LD_LIBRARY_PATH=$(CURDIR)/.deps/squash-prefix/lib:$$LD_LIBRARY_PATH; \
	jpg="$(HINT)/test_files111/fireworks.jpeg"; \
	txt="$(HINT)/test_files113/grammar.lsp"; \
	nci="$(HINT)/test_files118/nci"; \
	if [ -f "$$jpg" ]; then FZCODEC_DISABLE_PAQ=1 $(PLUGINTEST) --codec=fz-lifestyle "$$jpg"; \
	else echo "skip jpeg plugin file"; fi; \
	if [ -f "$$txt" ]; then env -u FZCODEC_DISABLE_PAQ $(PLUGINTEST) --codec=fz-lifestyle "$$txt"; \
	else echo "skip grammar plugin file"; fi; \
	if [ -f "$$nci" ]; then FZCODEC_DISABLE_PAQ=1 $(PLUGINTEST) --codec=fz-lifestyle "$$nci"; \
	else echo "skip nci plugin file"; fi

install: $(LIB) $(CLI)
	install -d "$(DESTDIR)$(PREFIX)/include" "$(DESTDIR)$(PREFIX)/lib/pkgconfig" "$(DESTDIR)$(PREFIX)/bin"
	install -m 644 include/fzcodec.h "$(DESTDIR)$(PREFIX)/include/"
	install -m 644 $(LIB) "$(DESTDIR)$(PREFIX)/lib/"
	install -m 755 $(CLI) "$(DESTDIR)$(PREFIX)/bin/fzcodec"
	printf '%s\n' \
		'prefix=$(PREFIX)' \
		'libdir=$${prefix}/lib' \
		'includedir=$${prefix}/include' \
		'' \
		'Name: fzcodec' \
		'Description: Content-aware compressor (lifestyle / ultra)' \
		'Version: 1.0.0' \
		'Libs: -L$${libdir} -lfzcodec -lzstd -lbrotlienc -lbrotlidec -lz -lpthread' \
		'Cflags: -I$${includedir}' \
		> "$(DESTDIR)$(PREFIX)/lib/pkgconfig/fzcodec.pc"

install-plugin: plugin
	install -d "$(DESTDIR)$(PREFIX)/lib/fzcodec-squash/fzcodec"
	install -m 755 $(PLUGIN) "$(DESTDIR)$(PREFIX)/lib/fzcodec-squash/fzcodec/"
	install -m 644 plugins/squash/squash.ini "$(DESTDIR)$(PREFIX)/lib/fzcodec-squash/fzcodec/"
	@echo "plugin installed; export SQUASH_PLUGINS=$(PREFIX)/lib/fzcodec-squash"

clean:
	rm -rf $(BUILD)
