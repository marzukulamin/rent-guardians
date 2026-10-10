// Mutation suites must never use the interactive app's database by default.
import assert from 'node:assert/strict';
export const testBase=process.env.RG_TEST_URL;
assert.ok(testBase,'Set RG_TEST_URL to a separate test server backed by a disposable test database.');
const response=await fetch(testBase+'/api/session');
assert.equal(response.status,200,'Test server is unavailable.');
assert.equal((await response.json()).environment,'test','Mutation suites require server environment=test; the demo database is protected.');
