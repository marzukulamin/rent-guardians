import test from 'node:test';
import assert from 'node:assert/strict';
import {execFileSync} from 'node:child_process';
test('administrator mutations obey database and audit safeguards and roll back',()=>{const result=execFileSync(process.env.RG_PHP_BIN||'E:/Xamppp/php/php.exe',['tests/admin-mutations.php'],{encoding:'utf8'});const report=JSON.parse(result.trim());assert.equal(report.rolled_back,true);assert.ok(report.passed>=20);});
