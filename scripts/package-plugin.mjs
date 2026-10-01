import { mkdir, rm } from 'node:fs/promises';
import { spawn } from 'node:child_process';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const outputDirectory = resolve(root, '..');
const outputFile = resolve(outputDirectory, 'ar-model-viewer-for-woocommerce.zip');

await new Promise((resolvePromise, reject) => {
    const status = spawn('git', ['diff', '--quiet', 'HEAD', '--'], { cwd: root });

    status.on('error', reject);
    status.on('close', (code) => {
        if (0 === code) {
            resolvePromise();
            return;
        }

        reject(new Error('The working tree must be clean before packaging.'));
    });
});

await mkdir(outputDirectory, { recursive: true });
await rm(outputFile, { force: true });

await new Promise((resolvePromise, reject) => {
    const archive = spawn(
        'git',
        [
            'archive',
            '--format=zip',
            '--prefix=ar-model-viewer-for-woocommerce/',
            '--output',
            outputFile,
            'HEAD',
            '--',
            '.',
            ':(exclude).gitignore',
            ':(exclude).phpdoc',
            ':(exclude)docs',
            ':(exclude)node_modules',
            ':(exclude)dist',
            ':(exclude)*.phar',
            ':(exclude).playwright-mcp',
            ':(exclude)scripts',
            ':(exclude)package.json',
            ':(exclude)package-lock.json',
            ':(exclude)sync-vendors.mjs',
        ],
        { cwd: root, stdio: 'inherit' }
    );

    archive.on('error', reject);
    archive.on('close', (code) => {
        if (0 === code) {
            resolvePromise();
            return;
        }

        reject(new Error(`git archive exited with code ${code}`));
    });
});

console.log(`Created ${outputFile}`);