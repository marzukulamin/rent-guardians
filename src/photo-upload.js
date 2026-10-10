// Bound uploaded property images for mobile loading; the server validates the result again.
export async function preparePropertyPhoto(file){
 if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>10*1024*1024)throw new Error('Choose a JPEG, PNG or WebP image up to 10 MB.');
 const bitmap=await createImageBitmap(file);
 try{
  if(bitmap.width*bitmap.height>40000000)throw new Error('Choose an image of at most 40 megapixels.');
  const scale=Math.min(1,2400/Math.max(bitmap.width,bitmap.height));const canvas=document.createElement('canvas');canvas.width=Math.max(1,Math.round(bitmap.width*scale));canvas.height=Math.max(1,Math.round(bitmap.height*scale));
  const context=canvas.getContext('2d');if(!context)throw new Error('Image preparation is unavailable in this browser.');context.drawImage(bitmap,0,0,canvas.width,canvas.height);
  const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/webp',.85));if(!blob)throw new Error('Could not prepare this photograph.');
  return new File([blob],file.name.replace(/\.[^.]+$/,'')+'.webp',{type:blob.type});
 }finally{bitmap.close();}
}
export const isDemoPhoto=(path,caption='')=>!!path&&(!path.startsWith('/files/photo/')||/demo|sample|illustrative|QA_TEST/i.test(caption));
