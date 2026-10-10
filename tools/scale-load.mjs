// Read-only HTTP smoke load. Run against a staging host for capacity measurements.
import {writeFileSync} from 'node:fs';
const base=process.env.RG_LOAD_BASE||'http://127.0.0.1:8000';
const count=Number(process.env.RG_LOAD_REQUESTS||200),concurrency=Number(process.env.RG_LOAD_CONCURRENCY||20);
if(!Number.isInteger(count)||count<1||count>100000||!Number.isInteger(concurrency)||concurrency<1||concurrency>500)throw new Error('Invalid load settings.');
const paths=['/api/properties?paged=1','/api/lookups','/api/properties?paged=1&location=Mirpur'];
let next=0,failures=0;const latencies=[],errors={};const start=performance.now();
await Promise.all(Array.from({length:concurrency},async()=>{while(next<count){const n=next++;const tick=performance.now();try{const response=await fetch(base+paths[n%paths.length],{signal:AbortSignal.timeout(30000)});const body=await response.json();if(!response.ok||body.error)throw new Error('HTTP '+response.status);}catch(e){failures++;errors[e.message]=(errors[e.message]||0)+1;}latencies.push(performance.now()-tick);}}));
latencies.sort((a,b)=>a-b);const duration=performance.now()-start;
const result={base,requests:count,concurrency,failures,errors,duration_ms:Math.round(duration),requests_per_second:Math.round(count/(duration/1000)),p50_ms:Math.round(latencies[Math.ceil(count*.5)-1]),p95_ms:Math.round(latencies[Math.ceil(count*.95)-1]),p99_ms:Math.round(latencies[Math.ceil(count*.99)-1]),note:'Public read smoke test, not 50,000 simultaneous users or authenticated write capacity.'};
writeFileSync(new URL('../docs/scale-http-results.json',import.meta.url),JSON.stringify(result,null,2)+'\n');console.log(JSON.stringify(result,null,2));if(failures)process.exitCode=1;
