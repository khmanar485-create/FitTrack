const API = "api/workouts.php";
let workouts = [];
let weeklyTarget = 4;
let csrfToken = document.querySelector("meta[name=csrf-token]")?.content || "";
let activeFilter = "all";

const $ = (id) => document.getElementById(id);
const today = new Date();
const dateISO = (date) => {
  const d = new Date(date);
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;
};
const mondayOf = (date) => {
  const d = new Date(date); d.setHours(0,0,0,0);
  d.setDate(d.getDate() - ((d.getDay()+6)%7)); return d;
};
const inCurrentWeek = (date) => {
  const d = new Date(`${date}T00:00:00`), start = mondayOf(today), end = new Date(start);
  end.setDate(end.getDate()+7); return d >= start && d < end;
};
const fmtDate = (date) => new Date(`${date}T00:00:00`).toLocaleDateString("en-US",{month:"short",day:"numeric"});
const escapeHTML = (value) => String(value ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));

async function loadWorkouts() {
  try {
    const response = await fetch(API, {headers:{"Accept":"application/json"}, credentials:"same-origin"});
    if (response.status === 401) { window.location.href = "login.php"; return; }
    if (!response.ok) throw new Error("Could not load your account data.");
    const data = await response.json();
    workouts = Array.isArray(data.workouts) ? data.workouts : [];
    weeklyTarget = Number(data.weeklyTarget || 4);
    csrfToken = data.csrf || csrfToken;
    renderAll();
  } catch (error) {
    showToast(error.message || "Unable to load your workouts. Please refresh.");
  }
}
async function persist() {
  try {
    const response = await fetch(API, {method:"POST", credentials:"same-origin", headers:{"Content-Type":"application/json", "X-CSRF-Token":csrfToken}, body:JSON.stringify({workouts, weeklyTarget})});
    if (response.status === 401) { window.location.href = "login.php"; return false; }
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || "Save failed.");
    return true;
  } catch (error) {
    showToast(error.message || "Your changes could not be saved. Please try again.");
    return false;
  }
}
function weekWorkouts(){return workouts.filter(w=>inCurrentWeek(w.date));}
function sum(list,key){return list.reduce((total,item)=>total+Number(item[key]||0),0);}
function showToast(message){const toast=$("toast");toast.textContent=message;toast.classList.add("show");setTimeout(()=>toast.classList.remove("show"),2600);}
function motivationalMessage(){
  const count=weekWorkouts().length, mins=sum(weekWorkouts(),"duration"), target=weeklyTarget;
  if(!workouts.length)return ["Your journey starts with one session.","You don't need a perfect plan. You just need to begin."];
  if(count>=target)return ["You showed up for yourself. That's a win.","You've reached your weekly target. Take a moment to feel proud."];
  if(count===0)return ["A fresh week. A fresh opportunity.","One workout is all it takes to build momentum."];
  if(count>=Math.ceil(target/2))return ["Look at you, building a rhythm.","You're more than halfway to your weekly commitment. Keep going."];
  if(mins>=90)return ["Your effort is adding up.","Every minute you invest is a promise kept to yourself."];
  return ["Progress is happening, even when it feels small.","Keep your promises to yourself, one session at a time."];
}
function renderAll(){
  const week=weekWorkouts(), count=week.length, minutes=sum(week,"duration"), calories=sum(week,"calories");
  $("todayLabel").textContent=today.toLocaleDateString("en-US",{weekday:"short",month:"short",day:"numeric"});
  $("statWorkouts").textContent=count; $("statMinutes").textContent=minutes.toLocaleString(); $("statCalories").textContent=calories.toLocaleString();
  const pct=Math.min(100,Math.round(count/weeklyTarget*100));
  $("statGoal").textContent=pct; $("goalFoot").textContent=`${count} / ${weeklyTarget} sessions`;
  $("goalRing").style.background=`conic-gradient(#75b794 ${pct*3.6}deg,#e9f0eb 0deg)`;
  $("ringPercent").textContent=pct+"%"; $("goalSummary").textContent=`${count} of ${weeklyTarget} workouts`;
  $("goalRemaining").textContent=count>=weeklyTarget?"Weekly goal achieved — beautifully done.":`${weeklyTarget-count} more to reach your goal`;
  const msg=motivationalMessage(); $("motivationText").textContent=msg[0]; $("motivationSub").textContent=msg[1];
  $("totalSessions").textContent=workouts.length; $("totalMinutes").textContent=sum(workouts,"duration").toLocaleString(); $("totalCalories").textContent=sum(workouts,"calories").toLocaleString();
  $("weeklyTarget").value=weeklyTarget; $("largeProgress").textContent=pct+"%"; $("progressBar").style.width=pct+"%";
  $("goalDetail").textContent=`${count} of ${weeklyTarget} workouts completed`;
  $("goalDetailSub").textContent=count>=weeklyTarget?"You kept your commitment this week.":`Only ${weeklyTarget-count} more ${weeklyTarget-count===1?"session":"sessions"} to go.`;
  $("firstMilestone").textContent=count>=1?"Complete":"Not yet"; $("halfMilestone").textContent=count>=Math.ceil(weeklyTarget/2)?"Complete":"Not yet"; $("finishMilestone").textContent=count>=weeklyTarget?"Complete":"Not yet";
  renderList("recentList",workouts.slice().sort((a,b)=>b.date.localeCompare(a.date)).slice(0,4),false);
  const shown=activeFilter==="week"?week:workouts.slice().sort((a,b)=>b.date.localeCompare(a.date));
  renderList("allWorkouts",shown,true); $("workoutCount").textContent=`${shown.length} ${shown.length===1?"session":"sessions"}`;
  renderCharts(); renderInsight();
}
function renderList(id,list,allowDelete){
  const el=$(id);
  if(!list.length){el.innerHTML=`<div class="empty-state">${id==="recentList"?"Your sessions will appear here once you log your first workout.":"No workouts found. Add a session to see it here."}</div>`;return;}
  el.innerHTML=list.map(w=>`<div class="workout-row"><div class="workout-symbol">${typeIcon(w.type)}</div><div class="workout-info"><strong>${escapeHTML(w.name)}</strong><span>${escapeHTML(w.type)} · ${fmtDate(w.date)} · ${escapeHTML(w.intensity||"Moderate")} intensity</span></div><div class="workout-meta"><strong>${Number(w.duration)} min</strong>${Number(w.calories).toLocaleString()} kcal</div>${allowDelete?`<button class="delete-workout" data-delete="${escapeHTML(w.id)}" title="Delete workout">×</button>`:""}</div>`).join("");
}
function typeIcon(type){return ({Strength:"↗",Cardio:"⌁",Running:"↝",Cycling:"◉",Yoga:"✧",Swimming:"≈",HIIT:"✦",Walking:"⌖"})[type]||"✳";}
function drawChart(canvasId,labels,values,kind="bar"){
  const canvas=$(canvasId); if(!canvas)return;
  const rect=canvas.getBoundingClientRect(), width=Math.max(280,rect.width), height=canvas.clientHeight||220, dpr=window.devicePixelRatio||1;
  canvas.width=width*dpr;canvas.height=height*dpr;
  const ctx=canvas.getContext("2d");ctx.scale(dpr,dpr);ctx.clearRect(0,0,width,height);
  const pad={l:30,r:10,t:15,b:30}, chartW=width-pad.l-pad.r, chartH=height-pad.t-pad.b;
  const max=Math.max(10,...values)*1.2;
  ctx.font='10px "DM Sans"';ctx.textAlign="right";ctx.fillStyle="#a2ada6";ctx.strokeStyle="#eef2ef";ctx.lineWidth=1;
  for(let i=0;i<4;i++){let y=pad.t+chartH*i/3;ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(width-pad.r,y);ctx.stroke();ctx.fillText(Math.round(max*(1-i/3)),pad.l-8,y+3);}
  const slot=chartW/Math.max(labels.length,1);
  if(kind==="line"){
    const pts=values.map((v,i)=>({x:pad.l+slot*(i+.5),y:pad.t+chartH-(v/max)*chartH}));
    if(pts.length){ctx.beginPath();pts.forEach((p,i)=>i?ctx.lineTo(p.x,p.y):ctx.moveTo(p.x,p.y));ctx.strokeStyle="#6eae8b";ctx.lineWidth=2.5;ctx.stroke();pts.forEach(p=>{ctx.beginPath();ctx.arc(p.x,p.y,3.5,0,Math.PI*2);ctx.fillStyle="#6eae8b";ctx.fill();});}
  }else{
    values.forEach((v,i)=>{const barW=Math.min(28,slot*.48),barH=(v/max)*chartH,x=pad.l+slot*i+(slot-barW)/2,y=pad.t+chartH-barH;ctx.fillStyle="#b8dfc9";roundRect(ctx,x,y,barW,barH,5);ctx.fill();});
  }
  ctx.textAlign="center";ctx.fillStyle="#98a49c";labels.forEach((label,i)=>ctx.fillText(label,pad.l+slot*(i+.5),height-8));
}
function roundRect(ctx,x,y,w,h,r){ctx.beginPath();ctx.moveTo(x+r,y);ctx.arcTo(x+w,y,x+w,y+h,r);ctx.arcTo(x+w,y+h,x,y+h,r);ctx.arcTo(x,y+h,x,y,r);ctx.arcTo(x,y,x+w,y,r);ctx.closePath();}
function lastSevenDays(){return Array.from({length:7},(_,i)=>{const d=new Date(today);d.setDate(d.getDate()-6+i);return d;});}
function renderCharts(){
  const days=lastSevenDays(),labels=days.map(d=>d.toLocaleDateString("en-US",{weekday:"short"}));
  const minutes=days.map(d=>sum(workouts.filter(w=>w.date===dateISO(d)),"duration"));
  const calories=days.map(d=>sum(workouts.filter(w=>w.date===dateISO(d)),"calories"));
  drawChart("weeklyChart",labels,minutes);drawChart("frequencyChart",labels,days.map(d=>workouts.filter(w=>w.date===dateISO(d)).length),"line");drawChart("calorieChart",labels,calories);
}
function renderInsight(){
  if(!workouts.length){$("insightTitle").textContent="Your journey starts with one session.";$("insightText").textContent="Log a workout and FitTrack will turn your activity into meaningful insights.";return;}
  const total=sum(workouts,"duration"),avg=Math.round(total/workouts.length),types={};
  workouts.forEach(w=>types[w.type]=(types[w.type]||0)+1);
  const favorite=Object.entries(types).sort((a,b)=>b[1]-a[1])[0][0];
  $("insightTitle").textContent=`${favorite} is your most frequent activity.`;
  $("insightText").textContent=`You've logged ${workouts.length} sessions and invested ${total.toLocaleString()} minutes. Your average workout lasts ${avg} minutes — keep building on that consistency.`;
}
function openModal(){
  $("workoutDate").value=dateISO(today);$("formError").textContent="";$("workoutModal").classList.add("open");$("workoutModal").setAttribute("aria-hidden","false");$("workoutName").focus();
}
function closeModal(){$("workoutModal").classList.remove("open");$("workoutModal").setAttribute("aria-hidden","true");$("workoutForm").reset();}
async function addWorkout(event){
  event.preventDefault();
  const duration=Number($("workoutDuration").value),calories=Number($("workoutCalories").value),pressure=Number($("workoutPressure").value);
  if(duration<1||calories<0||pressure<1||pressure>10){$("formError").textContent="Please check the duration, calories and effort values.";return;}
  const workout={id:crypto.randomUUID?crypto.randomUUID():String(Date.now()),name:$("workoutName").value.trim(),type:$("workoutType").value,date:$("workoutDate").value,duration,calories,intensity:$("workoutIntensity").value,pressure,notes:$("workoutNotes").value.trim()};
  if(!workout.name||!workout.date){$("formError").textContent="Please complete the required fields.";return;}
  workouts.push(workout);const saved=await persist();closeModal();renderAll();showToast(saved?"Workout saved to your account.":"Workout could not be saved. Please try again.");
}
async function deleteWorkout(id){
  if(!confirm("Delete this workout from your history?"))return;
  workouts=workouts.filter(w=>w.id!==id);const saved=await persist();renderAll();showToast(saved?"Workout deleted.":"Could not delete workout. Please try again.");
}
function switchView(name){
  document.querySelectorAll(".view").forEach(v=>v.classList.remove("active-view"));
  $(name+"View").classList.add("active-view");
  document.querySelectorAll(".nav-link").forEach(a=>a.classList.toggle("active",a.dataset.view===name));
  $("pageName").textContent=({overview:"Overview",workouts:"Workouts",progress:"Progress",goals:"Weekly goals"})[name];
  if(name==="progress")setTimeout(renderCharts,50);
  $("sidebar").classList.remove("open");
}
document.querySelectorAll(".nav-link").forEach(a=>a.addEventListener("click",e=>{e.preventDefault();switchView(a.dataset.view);}));
document.querySelectorAll("[data-go]").forEach(b=>b.addEventListener("click",()=>switchView(b.dataset.go)));
["quickAdd","addWorkoutTop","addWorkoutPage"].forEach(id=>$(id).addEventListener("click",openModal));
$("closeModal").addEventListener("click",closeModal);
$("workoutModal").addEventListener("click",e=>{if(e.target===$("workoutModal"))closeModal();});
$("workoutForm").addEventListener("submit",addWorkout);
$("allWorkouts").addEventListener("click",e=>{const btn=e.target.closest("[data-delete]");if(btn)deleteWorkout(btn.dataset.delete);});
document.querySelectorAll(".filter-tab").forEach(btn=>btn.addEventListener("click",()=>{activeFilter=btn.dataset.filter;document.querySelectorAll(".filter-tab").forEach(b=>b.classList.toggle("active",b===btn));renderAll();}));
$("goalMinus").addEventListener("click",()=>{$("weeklyTarget").value=Math.max(1,Number($("weeklyTarget").value)-1);});
$("goalPlus").addEventListener("click",()=>{$("weeklyTarget").value=Math.min(14,Number($("weeklyTarget").value)+1);});
$("goalForm").addEventListener("submit",async e=>{e.preventDefault();weeklyTarget=Math.max(1,Math.min(14,Number($("weeklyTarget").value)||4));const saved=await persist();renderAll();if(saved)showToast("Your weekly goal has been updated.");});
$("menuToggle").addEventListener("click",()=>$("sidebar").classList.toggle("open"));
window.addEventListener("resize",()=>{if(document.querySelector(".active-view")?.id==="overviewView"||document.querySelector(".active-view")?.id==="progressView")renderCharts();});
loadWorkouts();
