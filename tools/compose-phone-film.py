"""Reframe existing Omni clips and reflow typography for portrait phones; no generation."""
from pathlib import Path
import subprocess
from PIL import Image,ImageDraw,ImageFont
ROOT=Path(__file__).resolve().parents[1]
WORK=ROOT/'storage/brand-film';OUT=ROOT/'public/media/rent-guardian'
LAYERS=WORK/'phone-overlays';LAYERS.mkdir(exist_ok=True)
FF='C:/Users/ASUS/AppData/Local/Microsoft/WinGet/Packages/Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0-full_build/bin/ffmpeg.exe'
def font(size,bold=False):
 return ImageFont.truetype(str(ROOT/'public'/('pxiByp8kv8JHgFVrLEj6V1s.ttf' if bold else 'pxiEyp8kv8JHgFVrFJA.ttf')),size)
def run(args):subprocess.run([FF,'-hide_banner','-loglevel','error','-y',*map(str,args)],check=True,cwd=ROOT)
messages=[('YOUR NEXT CHAPTER','Your next home\nstarts here.','Find a place. Feel at home.'),
 ('FIND A HOME','Find a home\nwith confidence.','A clearer view of your next chapter.'),
 ('KNOW YOUR HOME','Know your home\nbefore you move.','Room condition. Fixtures.\nInspection reports.'),
 ('KEEP IT TOGETHER','Rent and records,\ntogether.','Agreements, monthly bills\nand payment records.'),
 ('EVERYDAY SUPPORT','A little help for\neveryday life.','Maintenance, groceries\nand meal plans.')]
for i,(kicker,title,sub) in enumerate(messages):
 im=Image.new('RGBA',(720,1560));d=ImageDraw.Draw(im)
 d.rounded_rectangle((40,815,680,1130),radius=25,fill=(21,59,88,235))
 d.text((70,841),kicker,font=font(19,True),fill=(104,213,245))
 d.multiline_text((70,887),title,font=font(38,True),fill='white',spacing=9)
 d.multiline_text((70,1020),sub,font=font(23),fill=(211,229,241),spacing=6)
 im.save(LAYERS/f'message-{i}.png')
close=Image.new('RGBA',(720,1560),(21,22,39,238))
logo=Image.open(ROOT/'public/brand-logo.png').convert('RGBA').resize((480,480),Image.Resampling.LANCZOS)
close.alpha_composite(logo,(120,300));d=ImageDraw.Draw(close)
d.text((360,850),'Your home,',anchor='mm',font=font(40,True),fill='white')
d.text((360,915),'in good hands.',anchor='mm',font=font(40,True),fill='white')
d.text((360,1040),'FIND A HOME / LIST YOUR PROPERTY',anchor='mm',font=font(18),fill=(158,218,242))
close.save(LAYERS/'closing.png')
inputs=['-i',WORK/'sources/entrance-01.mp4','-i',WORK/'sources/entrance-02.mp4']
for i in range(5):inputs+=['-loop','1','-i',LAYERS/f'message-{i}.png']
inputs+=['-loop','1','-i',LAYERS/'closing.png']
filters=[]
for i,label in [(0,'a'),(1,'b')]:
 filters.append(f'[{i}:v]scale=1920:1080,crop=498:1080:(iw-ow)/2:0,scale=720:1560,setsar=1,fps=30,trim=duration=9,setpts=PTS-STARTPTS[{label}]')
filters.append('[a][b]concat=n=2:v=1:a=0[base]');last='base'
for i in range(5):
 start=i*3;end=start+3
 filters.append(f'[{i+2}:v]format=rgba,fade=t=in:st={start}:d=0.28:alpha=1,fade=t=out:st={end-0.28}:d=0.28:alpha=1[layer{i}]')
 filters.append(f'[{last}][layer{i}]overlay=0:0:enable=\'between(t,{start},{end})\'[v{i}]');last=f'v{i}'
filters+=['[7:v]format=rgba,fade=t=in:st=15:d=0.6:alpha=1[close]',f'[{last}][close]overlay=0:0:enable=\'gte(t,15)\',format=yuv420p[final]']
run([*inputs,'-filter_complex',';'.join(filters),'-map','[final]','-an','-t','18','-c:v','libx264','-preset','medium','-crf','25','-movflags','+faststart',OUT/'intro-phone.mp4'])
run(['-ss','0.5','-i',OUT/'intro-phone.mp4','-frames:v','1','-q:v','3',OUT/'intro-phone-poster.jpg'])
run(['-i',OUT/'intro-phone.mp4','-vf','fps=1/3,scale=240:-1,tile=3x2','-frames:v','1',WORK/'qa/phone-contact-sheet.jpg'])
print('Portrait film:',(OUT/'intro-phone.mp4').stat().st_size,'bytes')
