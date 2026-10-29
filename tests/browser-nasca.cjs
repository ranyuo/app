/* Ejecutar con Playwright/Chromium externos y una instalación local de nasca_test. */
const {chromium}=require('playwright');
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8083';
assert.ok(['127.0.0.1','localhost'].includes(new URL(base).hostname));
const seed=JSON.parse(fs.readFileSync(path.join(__dirname,'../portal-private/data/portal.json')));
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox'],headless:true});
 const errors=[],routes=['','nosotros/presentacion','nosotros/resena-historica','nosotros/mision-vision','nosotros/organizacion','nosotros/plana-jerarquica','nosotros/plana-docente','nosotros/campus-e-informes','programas',...seed.programas.map(p=>'programas/'+p.slug),'admision','matricula','becas','noticias','transparencia/documentos-gestion','transparencia/tupa','transparencia/estadisticas','transparencia/inversiones','transparencia/licenciamiento','transparencia/convocatorias','servicios/complementarios','servicios/mesa-de-partes','servicios/libro-de-reclamaciones','servicios/consulta-certificados','servicios/bolsa-laboral','contacto','preguntas-frecuentes'];
 let checks=0;
 try{
  const page=await browser.newPage({viewport:{width:1365,height:900},reducedMotion:'reduce'});
  page.on('pageerror',e=>errors.push(e.message));
  await page.route('https://**/*',r=>r.abort());
  for(const route of routes){
   console.log('Ruta:',route||'/');await page.goto(base+'/#/'+route,{waitUntil:'domcontentloaded',timeout:15000});await page.locator('main').waitFor();
   const text=await page.locator('main').innerText();
   assert.ok(text.length>70,route);assert.doesNotMatch(text,/Chincha|Doris Elizabeth|Pueblo Nuevo|Profesional de Ejemplo|1967|Página no encontrada/i,route);checks++;
  }
  for(const program of seed.programas){
   await page.goto(base+'/#/programas/'+program.slug);
   assert.ok((await page.locator('main').innerText()).includes(program.nombre));
   const popup=page.waitForEvent('popup');await page.locator('[data-doc=malla]').click();
   const opened=await popup;assert.ok(opened.url().includes('/assets/nasca/'));await opened.close();checks++;
  }
  await page.goto(base+'/#/admision');assert.equal(await page.locator('[data-res]').count(),8);checks++;
  assert.ok((await page.locator('main').innerText()).includes('difiere del prospecto'));checks++;
  await page.goto(base+'/#/transparencia/estadisticas?anio=2025');
  const total=await page.locator('main tbody tr').last().innerText();assert.ok(total.includes('406'));assert.ok(total.includes('—'));checks++;
  await page.goto(base+'/#/buscar?q=turismo');assert.ok((await page.locator('main').innerText()).includes('Guía Oficial de Turismo'));checks++;
  await page.setViewportSize({width:390,height:844});
  for(const route of ['','programas','admision','becas','transparencia/tupa','nosotros/plana-docente']){
   console.log('Ruta:',route||'/');await page.goto(base+'/#/'+route,{waitUntil:'domcontentloaded',timeout:15000});await page.locator('main').waitFor();
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'Desborde móvil: '+route);checks++;
  }
  for(const file of [seed.config.inst.logo,seed.programas[0].malla.url,seed.admisiones[0].prospecto.url,seed.documentos.find(d=>d.archivo.type.includes('word')).archivo.url]){
   const response=await page.request.get(base+file);assert.equal(response.status(),200,file);assert.ok((await response.body()).length>1000);checks++;
  }
  await page.setViewportSize({width:1365,height:900});await page.goto(base+'/');
  await page.evaluate(()=>Promise.all([...document.images].map(i=>{i.loading='eager';return i.decode().catch(()=>{});})));
  const broken=await page.locator('main img').evaluateAll(imgs=>imgs.filter(i=>!i.complete||!i.naturalWidth).map(i=>i.src));assert.deepEqual(broken,[]);checks++;
  fs.mkdirSync(path.join(__dirname,'.results/nasca'),{recursive:true});await page.screenshot({path:path.join(__dirname,'.results/nasca/home-desktop.png'),fullPage:true});
  assert.deepEqual(errors,[]);
  console.log(`${checks} comprobaciones web de Nasca aprobadas; ${routes.length} rutas, 8 mallas descargables, resultados, búsqueda y móvil; sin errores JavaScript.`);
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
