const assert=require('node:assert/strict');
module.exports=async function requireTestEnvironment(){
 const base=process.env.RG_TEST_URL;
 assert.ok(base,'Set RG_TEST_URL to a separate disposable test server.');
 const response=await fetch(base+'/api/session');
 assert.equal(response.status,200,'Test server is unavailable.');
 assert.equal((await response.json()).environment,'test','Fixture-writing UI tests require environment=test.');
 return base;
};
