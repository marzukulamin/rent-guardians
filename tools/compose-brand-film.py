"""Rebuild the Rent Guardian film and web assets from reviewed OmniFlash sources."""
from pathlib import Path
import json, subprocess
from PIL import Image, ImageDraw, ImageFont

ROOT=Path(__file__).resolve().parents[1]
WORK=ROOT/'storage/brand-film'
OUT=ROOT/'public/media/rent-guardian'
FF=Path('C:/Users/ASUS/AppData/Local/Microsoft/WinGet/Packages/Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0-full_build/bin/ffmpeg.exe')
OUT.mkdir(parents=True,exist_ok=True)
LAYER=WORK/'overlays';LAYER.mkdir(exist_ok=True)
def font(size,bold=False):
 return ImageFont.truetype(str(ROOT/'public'/('pxiByp8kv8JHgFVrLEj6V1s.ttf' if bold else 'pxiEyp8kv8JHgFVrFJA.ttf')),size)
def run(args):
 subprocess.run([str(FF),'-hide_banner','-loglevel','error','-y',*map(str,args)],check=True,cwd=ROOT)

# Transparent full-frame layers give exact typography and reusable editing sources.
messages=[('YOUR NEXT CHAPTER','Your next home starts here.','Find a place. Feel at home.',0,3),
 ('FIND A HOME','Find a home with confidence.','A clearer view of your next chapter.',3,6),
 ('KNOW YOUR HOME','Know your home before you move.','Room condition. Fixtures. Inspection reports.',6,9),
 ('KEEP IT TOGETHER','Rent and records, together.','Agreements, monthly bills and payment records.',9,12),
 ('EVERYDAY SUPPORT','A little help for everyday life.','Maintenance, groceries and meal plans.',12,15)]
for i,(kicker,title,sub,start,end) in enumerate(messages):
 im=Image.new('RGBA',(1920,1080));d=ImageDraw.Draw(im)
 d.rounded_rectangle((90,790,1590,1010),radius=25,fill=(21,59,88,225))
 d.text((130,815),kicker,font=font(19,True),fill=(104,213,245))
 d.text((130,854),title,font=font(43,True),fill='white')
 d.text((130,924),sub,font=font(24),fill=(211,229,241))
 # Graphic representations of existing app workflows, with no invented account data.
 if i>=2:
  labels={2:['Room condition','Plumbing','Electrical'],3:['Rental agreement','Monthly bill','Payment record'],4:['Maintenance','Groceries','Meal plans']}[i]
  for n,label in enumerate(labels):
   x=1040+n*255
   d.rounded_rectangle((x,130,x+235,221),radius=15,fill=(248,251,255,239))
   d.ellipse((x+17,151,x+31,165),fill=(44,132,180))
   d.text((x+17,177),label,font=font(17,True),fill=(21,59,88))
 im.save(LAYER/f'message-{i}.png')

closing=Image.new('RGBA',(1920,1080),(21,22,39,225))
logo=Image.open(ROOT/'public/brand-logo.png').convert('RGBA').resize((480,480),Image.Resampling.LANCZOS)
closing.alpha_composite(logo,(720,205));d=ImageDraw.Draw(closing)
text='Your home, in good hands.'
d.text((960,735),text,anchor='mm',font=font(46,True),fill='white')
d.text((960,830),'FIND A HOME  /  LIST YOUR PROPERTY',anchor='mm',font=font(21),fill=(158,218,242))
closing.save(LAYER/'closing.png')

inputs=['-i',WORK/'sources/entrance-01.mp4','-i',WORK/'sources/entrance-02.mp4']
for i in range(5):inputs+=['-loop','1','-i',LAYER/f'message-{i}.png']
inputs+=['-loop','1','-i',LAYER/'closing.png']
filters=['[0:v]scale=1920:1080,fps=30,trim=duration=9,setpts=PTS-STARTPTS[a]',
 '[1:v]scale=1920:1080,fps=30,trim=duration=9,setpts=PTS-STARTPTS[b]',
 '[a][b]concat=n=2:v=1:a=0[base]']
last='base'
for i,(_,_,_,start,end) in enumerate(messages):
 filters.append(f'[{i+2}:v]format=rgba,fade=t=in:st={start}:d=0.28:alpha=1,fade=t=out:st={end-0.28}:d=0.28:alpha=1[layer{i}]')
 filters.append(f'[{last}][layer{i}]overlay=0:0:enable=\'between(t,{start},{end})\'[v{i}]');last=f'v{i}'
filters+=['[7:v]format=rgba,fade=t=in:st=15:d=0.6:alpha=1[close]',f'[{last}][close]overlay=0:0:enable=\'gte(t,15)\',format=yuv420p[final]']
run([*inputs,'-filter_complex',';'.join(filters),'-map','[final]','-an','-t','18','-c:v','libx264','-preset','medium','-crf','19','-movflags','+faststart',WORK/'rent-guardian-master-18.mp4'])
run(['-i',WORK/'rent-guardian-master-18.mp4','-vf','scale=1280:720','-an','-c:v','libx264','-preset','medium','-crf','24','-movflags','+faststart',OUT/'intro-web.mp4'])
# A deterministic forward/reverse loop removes any endpoint jump in the AI camera drift.
run(['-i',WORK/'sources/hero-07.mp4','-filter_complex','[0:v]trim=duration=3.5,setpts=PTS-STARTPTS,fps=30,scale=960:540,split[a][b];[b]reverse[r];[a][r]concat=n=2:v=1:a=0,format=yuv420p[v]','-map','[v]','-an','-t','7','-c:v','libx264','-preset','medium','-crf','25','-movflags','+faststart',OUT/'hero-web.mp4'])
for name,source,t in [('intro-poster',OUT/'intro-web.mp4',0.5),('hero-poster',OUT/'hero-web.mp4',0)]:
 run(['-ss',t,'-i',source,'-frames:v','1','-q:v','3',OUT/f'{name}.jpg'])
run(['-i',WORK/'rent-guardian-master-18.mp4','-vf','fps=1,scale=480:-1,tile=6x3','-frames:v','1',WORK/'qa/final-contact-sheet.jpg'])
print(json.dumps({p.name:p.stat().st_size for p in OUT.iterdir()},indent=2))
