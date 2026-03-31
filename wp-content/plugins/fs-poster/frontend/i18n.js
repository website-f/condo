const fs = require("fs");
const path = require("path");
const gettextParser = require("gettext-parser");
const fg = require("fast-glob");
const babelParser = require("@babel/parser");
const traverse = require("@babel/traverse").default;

const plugins = [
    {
        slug: "fs-poster",
        root: path.resolve(__dirname, "../"),
        functionName: "fsp__"
    }/*,
    {
        slug: "fs-poster-pro",
        root: path.resolve(__dirname, "../../fs-poster-pro/"),
        functionName: "fsppro__"
    }*/
];

async function extractJSStrings(pluginRoot, functionName) {
    const srcDir = path.join(pluginRoot, "frontend/src");
    const patterns = [`${srcDir}/**/*.{js,tsx,ts}`];
    const files = await fg(patterns, {
        ignore: ["**/node_modules/**"]
    });

    const entries = new Map();

    for (const file of files) {
        const code = fs.readFileSync(file, "utf8");
        let ast;
        try {
            ast = babelParser.parse(code, {
                sourceType: "module",
                plugins: ["jsx", "typescript"]
            });
        } catch (e) {
            console.warn(`[-] JS parse error in ${file}: ${e.message}`);
            continue;
        }

        traverse(ast, {
            CallExpression(pathNode) {
                const callee = pathNode.node.callee;
                if (
                    callee.type === "Identifier" &&
                    callee.name === functionName &&
                    pathNode.node.arguments.length > 0
                ) {
                    const arg = pathNode.node.arguments[0];
                    let text = null;

                    if (arg.type === "StringLiteral") text = arg.value;
                    else if (arg.type === "TemplateLiteral" && arg.expressions.length === 0) {
                        text = arg.quasis.map(q => q.value.cooked).join("");
                    }

                    if (text) {
                        const relative = path.relative(process.cwd(), file);
                        if (!entries.has(text)) entries.set(text, []);
                        entries.get(text).push(`${relative}:${arg.loc?.start.line || 0}`);
                    }
                }
            }
        });
    }

    return entries;
}

function updatePoMo(potPath, langDir, slug) {
    const potContent = fs.readFileSync(potPath);
    const pot = gettextParser.po.parse(potContent);

    const poFiles = fs.readdirSync(langDir)
        .filter(f => f.startsWith(slug + "-") && f.endsWith(".po"));

    for (const poFile of poFiles) {
        const poPath = path.join(langDir, poFile);
        const poContent = fs.readFileSync(poPath);
        const po = gettextParser.po.parse(poContent);

        const existing = po.translations[""] || {};
        const potEntries = pot.translations[""];

        for (const msgid in potEntries) {
            if (!existing[msgid]) {
                existing[msgid] = {
                    msgid,
                    msgstr: [""],
                    comments: potEntries[msgid].comments || {}
                };
            }
        }

        po.translations[""] = existing;

        const newPo = gettextParser.po.compile(po);
        fs.writeFileSync(poPath, newPo);

        const moFileName = poFile.replace(/\.po$/, ".mo");
        const moPath = path.join(langDir, moFileName);
        const mo = gettextParser.mo.compile(po);
        fs.writeFileSync(moPath, mo);

        console.log(`[+] ${poFile} & ${moFileName} updated`);
    }
}

const { spawnSync } = require("child_process");

function runXgettext(slug, pluginRoot, functionName, potPath) {
    const phpDir = path.join(pluginRoot, "App");

    const phpFiles = fg.sync(["App/**/*.php"], {
        cwd: pluginRoot,
        absolute: false
    });

    if (phpFiles.length === 0) {
        console.warn(`[-] No php files found: ${phpDir}`);
        return;
    }

    const xgettextArgs = [
        "--language=PHP",
        "--from-code=UTF-8",
        `--keyword=${functionName}`,
        `--output=${potPath}`,
        `--directory=${pluginRoot}`,
        ...phpFiles
    ];

    const result = spawnSync("xgettext", xgettextArgs, { stdio: "inherit" });

    if (result.status !== 0) {
        console.error(`[-] xgettext error for ${slug}`);
    } else {
        console.log(`[+] php strings added: ${path.basename(potPath)}`);
    }
}



(async () => {
    for (const plugin of plugins) {
        const { slug, root, functionName } = plugin;
        const langDir = path.join(root, "languages");
        const potPath = path.join(langDir, `${slug}.pot`);

        console.log(`\n[+] Translating plugin: ${slug}`);

        runXgettext(slug, root, functionName, potPath);

        const jsStrings = await extractJSStrings(root, functionName);
        if (jsStrings.size > 0) {
            const pot = fs.existsSync(potPath)
                ? gettextParser.po.parse(fs.readFileSync(potPath))
                : {
                    charset: "UTF-8",
                    headers: { "content-type": "text/plain; charset=UTF-8" },
                    translations: { "": {} }
                };

            const translations = pot.translations[""];

            for (const [msgid, refs] of jsStrings.entries()) {
                if (!translations[msgid]) {
                    translations[msgid] = {
                        msgid,
                        msgstr: [""],
                        comments: { reference: refs.join("\n") }
                    };
                } else {
                    const existingRefs = translations[msgid].comments?.reference?.split("\n") || [];
                    const allRefs = Array.from(new Set([...existingRefs, ...refs]));
                    translations[msgid].comments = { reference: allRefs.join("\n") };
                }
            }

            const compiled = gettextParser.po.compile(pot);
            fs.writeFileSync(potPath, compiled);
            console.log(`[+] js/ts/tsx strings added: ${slug}.pot`);
        }

        updatePoMo(potPath, langDir, slug);
    }

    console.log("\n[+] done");
})();
