

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>JLPT Vocabulary Hunter</title>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
body{
    font-family: 'Segoe UI', sans-serif;
    background:
        radial-gradient(circle at top left, rgba(99, 102, 241, 0.18), transparent 30%),
        radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.18), transparent 30%),
        #f8fafc;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:0;
    min-height: 100vh;
}
 
.container{
    width:500px;
    max-width:100%;
    background:
        radial-gradient(circle at top left, rgba(20, 23, 204, 0.856), transparent 30%),
        radial-gradient(circle at bottom right, rgba(114, 228, 21, 0.705), transparent 30%),
        #f8fafc;
    padding:30px;
    border-radius:25px;
    box-shadow:0 15px 40px rgba(0,0,0,.2);
    text-align:center;
}
 
.back-btn{
    display:flex;
    align-items:center;
    gap:8px;

    padding:10px 16px;

    background:
        radial-gradient(circle at top left, rgba(20, 23, 204, 0.856), transparent 30%),
        radial-gradient(circle at bottom right, rgba(114, 228, 21, 0.705), transparent 30%),
        #a3d0ee;

    text-decoration:none;
    border-radius:12px;

    font-size:14px;
    font-weight:600;

    border:1px solid rgba(255,255,255,0.08);

    transition:0.2s ease;
}

.back-btn:hover{
    background:#374151;
    color:#fff;
    transform:translateY(-1px);
}

.back-btn i{
    font-size:16px;
}

h1{
    color:#333;
}

#menu{
    text-align:center;
}

#menu h3{
    margin-bottom:20px;
    color:#333;
    font-weight:700;
}

.level-btn{
    width:120px;
    height:50px;
    margin:10px;
    border:none;
    border-radius:15px;
    font-size:18px;
    font-weight:bold;
    color:white;
    cursor:pointer;
    transition:all 0.3s ease;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

.level-btn:nth-child(2){
    background:#28a745; /* N5 */
}

.level-btn:nth-child(3){
    background:#17a2b8; /* N4 */
}

.level-btn:nth-child(4){
    background:#ffc107; /* N3 */
    color:black;
}

.level-btn:nth-child(5){
    background:#fd7e14; /* N2 */
}

.level-btn:nth-child(6){
    background:#dc3545; /* N1 */
}

.level-btn:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 20px rgba(0,0,0,0.3);
}

.level-btn:active{
    transform:scale(0.95);
}
 
#word{
    font-size:42px;
    color:#ff63b6;
    margin:25px 0;
}
 
.top-bar{
    display:flex;
    justify-content:space-between;
    margin-bottom:20px;
}
 
.score{
    color:#27ae60;
    font-weight:bold;
}
 
.timer{
    color:#e74c3c;
    font-weight:bold;
}
 
#answers{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
    margin-top:20px;
}
 
.answer-btn{
    padding:18px;
    border:none;
    border-radius:15px;
    font-size:18px;
    font-weight:bold;
    background:#f8f9fa;
    cursor:pointer;
    transition:all .3s;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}
 
.answer-btn:hover{
    transform:translateY(-5px);
    background:#7d7075;
    color:white;
}
 
.correct{
    background:#2ecc71 !important;
    color:white;
}
 
.wrong{
    background:#e74c3c !important;
    color:white;
}
 
.progress{
    width:100%;
    height:15px;
    background:#ddd;
    border-radius:20px;
    overflow:hidden;
}
 
.progress-bar{
    width:0%;
    height:100%;
    background:#6c63ff;
    transition:0.5s;
}
 
@media(max-width:600px){
    #answers{
        grid-template-columns:1fr;
    }
}
#game{
    display:none;
}
#resultMessage{
    margin-top:20px;
    font-size:22px;
    font-weight:bold;
    min-height:40px;
}
#resultMessage{
    margin-top:20px;
    padding:15px;
    border-radius:10px;
    background:#f8f9fa;
    font-size:22px;
    font-weight:bold;
}
</style>
</head>
<body>
 
    <div style="position:absolute; top:20px; left:20px;">
        <a href="javascript:history.back()" class="back-btn">
            <i class="bi bi-arrow-left"></i>
            戻る
        </a>
    </div>

<div class="container">
 
<h1>🎮 JLPT Vocabulary Hunter</h1>
 
<div id="menu">
    <h3>Select Level</h3>
 
    <button class="level-btn" onclick="startGame('N5')">N5</button>
    <button class="level-btn" onclick="startGame('N4')">N4</button>
    <button class="level-btn" onclick="startGame('N3')">N3</button>
    <button class="level-btn" onclick="startGame('N2')">N2</button>
    <button class="level-btn" onclick="startGame('N1')">N1</button>
</div>
 
<div id="game">
 
<h2 id="levelTitle"></h2>
 
<div class="progress">
    <div class="progress-bar" id="progress"></div>
</div>
 
<h3>⏱ Time: <span id="timer">15</span></h3>
 
<h3>⭐ Score: <span id="score">0</span></h3>
 
<h1 id="word"></h1>
 
<div id="answers"></div>
<div id="resultMessage"></div>
 
</div>
 
</div>
 
<script>
 
const data = {
 
N5:[
{word:"学校",correct:"School",options:["School","Hospital","Airport","Bank"]},
{word:"先生",correct:"Teacher",options:["Teacher","Doctor","Chef","Driver"]},
{word:"本",correct:"Book",options:["Book","Pen","Bag","Phone"]},
{word:"車",correct:"Car",options:["Car","Train","Bicycle","Plane"]},
{word:"猫",correct:"Cat",options:["Cat","Dog","Bird","Fish"]},
{word:"犬",correct:"Dog",options:["Dog","Cat","Horse","Bird"]},
{word:"水",correct:"Water",options:["Water","Milk","Coffee","Tea"]},
{word:"山",correct:"Mountain",options:["Mountain","River","Ocean","Forest"]},
{word:"友達",correct:"Friend",options:["Friend","Teacher","Doctor","Boss"]},
{word:"家",correct:"House",options:["House","School","Store","Hotel"]},
 
{word:"学生",correct:"Student",options:["Student","Teacher","Doctor","Driver"]},
{word:"会社",correct:"Company",options:["Company","School","Hospital","Airport"]},
{word:"駅",correct:"Station",options:["Station","Market","Library","Park"]},
{word:"病院",correct:"Hospital",options:["Hospital","School","Bank","Restaurant"]},
{word:"銀行",correct:"Bank",options:["Bank","Airport","Hotel","Museum"]},
{word:"郵便局",correct:"Post Office",options:["Post Office","Police Station","School","Market"]},
{word:"公園",correct:"Park",options:["Park","Hospital","Bank","Station"]},
{word:"店",correct:"Shop",options:["Shop","School","Airport","Temple"]},
{word:"食堂",correct:"Cafeteria",options:["Cafeteria","Library","Office","Factory"]},
{word:"図書館",correct:"Library",options:["Library","Station","Restaurant","Park"]},
 
{word:"食べる",correct:"Eat",options:["Eat","Drink","Read","Write"]},
{word:"飲む",correct:"Drink",options:["Drink","Eat","Sleep","Walk"]},
{word:"見る",correct:"See",options:["See","Hear","Speak","Write"]},
{word:"聞く",correct:"Listen",options:["Listen","Read","Sleep","Run"]},
{word:"話す",correct:"Speak",options:["Speak","Write","Eat","Buy"]},
{word:"書く",correct:"Write",options:["Write","Read","Drink","Listen"]},
{word:"読む",correct:"Read",options:["Read","Write","Walk","Run"]},
{word:"行く",correct:"Go",options:["Go","Come","Stay","Return"]},
{word:"来る",correct:"Come",options:["Come","Go","Leave","Wait"]},
{word:"帰る",correct:"Return",options:["Return","Arrive","Sleep","Study"]},
 
{word:"朝",correct:"Morning",options:["Morning","Evening","Night","Noon"]},
{word:"昼",correct:"Noon",options:["Noon","Morning","Night","Tomorrow"]},
{word:"夜",correct:"Night",options:["Night","Morning","Afternoon","Yesterday"]},
{word:"今日",correct:"Today",options:["Today","Tomorrow","Yesterday","Morning"]},
{word:"明日",correct:"Tomorrow",options:["Tomorrow","Today","Yesterday","Night"]},
{word:"昨日",correct:"Yesterday",options:["Yesterday","Tomorrow","Today","Morning"]},
{word:"時間",correct:"Time",options:["Time","Money","Book","Ticket"]},
{word:"毎日",correct:"Every Day",options:["Every Day","Every Week","Every Month","Every Year"]},
{word:"今週",correct:"This Week",options:["This Week","Next Week","Last Week","This Month"]},
{word:"来年",correct:"Next Year",options:["Next Year","Last Year","This Year","Tomorrow"]},
 
{word:"赤",correct:"Red",options:["Red","Blue","Green","Black"]},
{word:"青",correct:"Blue",options:["Blue","Red","White","Yellow"]},
{word:"白",correct:"White",options:["White","Black","Blue","Red"]},
{word:"黒",correct:"Black",options:["Black","White","Yellow","Green"]},
{word:"大きい",correct:"Big",options:["Big","Small","Fast","Slow"]},
{word:"小さい",correct:"Small",options:["Small","Big","Heavy","Long"]},
{word:"新しい",correct:"New",options:["New","Old","Cheap","Expensive"]},
{word:"古い",correct:"Old",options:["Old","New","Young","Modern"]},
{word:"高い",correct:"Expensive",options:["Expensive","Cheap","Small","Light"]},
{word:"安い",correct:"Cheap",options:["Cheap","Expensive","Heavy","Tall"]}
],  
 
N4:[
{word:"経験",correct:"Experience",options:["Experience","Food","Weather","Train"]},
{word:"約束",correct:"Promise",options:["Promise","Money","Book","Car"]},
{word:"準備",correct:"Preparation",options:["Preparation","Accident","Medicine","Music"]},
{word:"説明",correct:"Explanation",options:["Explanation","Question","Answer","Picture"]},
{word:"連絡",correct:"Contact",options:["Contact","Travel","Exercise","Repair"]},
{word:"必要",correct:"Necessary",options:["Necessary","Dangerous","Beautiful","Cold"]},
{word:"失敗",correct:"Failure",options:["Failure","Success","Victory","Reward"]},
{word:"案内",correct:"Guide",options:["Guide","Map","Food","Friend"]},
{word:"安全",correct:"Safety",options:["Safety","Danger","War","Accident"]},
{word:"習慣",correct:"Habit",options:["Habit","Festival","Game","Journey"]},
 
{word:"受付",correct:"Reception",options:["Reception","Kitchen","Classroom","Library"]},
{word:"都合",correct:"Convenience",options:["Convenience","Weather","Money","Health"]},
{word:"利用",correct:"Use",options:["Use","Throw Away","Lose","Hide"]},
{word:"返事",correct:"Reply",options:["Reply","Question","Problem","Gift"]},
{word:"紹介",correct:"Introduction",options:["Introduction","Competition","Accident","Travel"]},
{word:"予定",correct:"Schedule",options:["Schedule","History","Memory","Dream"]},
{word:"試験",correct:"Examination",options:["Examination","Holiday","Festival","Game"]},
{word:"卒業",correct:"Graduation",options:["Graduation","Employment","Marriage","Travel"]},
{word:"残業",correct:"Overtime Work",options:["Overtime Work","Vacation","Exercise","Meeting"]},
{word:"会議",correct:"Meeting",options:["Meeting","Concert","Lesson","Journey"]},
 
{word:"出席",correct:"Attendance",options:["Attendance","Absence","Vacation","Escape"]},
{word:"欠席",correct:"Absence",options:["Absence","Attendance","Arrival","Departure"]},
{word:"運転",correct:"Driving",options:["Driving","Cooking","Reading","Studying"]},
{word:"故障",correct:"Breakdown",options:["Breakdown","Success","Growth","Victory"]},
{word:"修理",correct:"Repair",options:["Repair","Damage","Destruction","Loss"]},
{word:"荷物",correct:"Luggage",options:["Luggage","Ticket","Passport","Money"]},
{word:"切符",correct:"Ticket",options:["Ticket","Bag","Phone","Book"]},
{word:"予約",correct:"Reservation",options:["Reservation","Cancellation","Complaint","Invitation"]},
{word:"招待",correct:"Invitation",options:["Invitation","Punishment","Order","Demand"]},
{word:"訪問",correct:"Visit",options:["Visit","Departure","Escape","Failure"]},
 
{word:"相談",correct:"Consultation",options:["Consultation","Argument","Competition","Fight"]},
{word:"確認",correct:"Confirmation",options:["Confirmation","Cancellation","Refusal","Attack"]},
{word:"原因",correct:"Cause",options:["Cause","Result","Reward","Gift"]},
{word:"結果",correct:"Result",options:["Result","Reason","Question","Answer"]},
{word:"選択",correct:"Choice",options:["Choice","Failure","Damage","Travel"]},
{word:"決定",correct:"Decision",options:["Decision","Question","Problem","Accident"]},
{word:"理由",correct:"Reason",options:["Reason","Result","Gift","Reward"]},
{word:"可能",correct:"Possible",options:["Possible","Impossible","Dangerous","Expensive"]},
{word:"不可能",correct:"Impossible",options:["Impossible","Possible","Easy","Safe"]},
{word:"重要",correct:"Important",options:["Important","Unnecessary","Weak","Small"]},
 
{word:"親切",correct:"Kind",options:["Kind","Angry","Lazy","Selfish"]},
{word:"丁寧",correct:"Polite",options:["Polite","Rude","Dangerous","Careless"]},
{word:"元気",correct:"Healthy",options:["Healthy","Sick","Weak","Sad"]},
{word:"心配",correct:"Worry",options:["Worry","Joy","Victory","Hope"]},
{word:"安心",correct:"Relief",options:["Relief","Fear","Danger","Stress"]},
{word:"最近",correct:"Recently",options:["Recently","Ancient","Future","Tomorrow"]},
{word:"将来",correct:"Future",options:["Future","Past","History","Memory"]},
{word:"途中",correct:"On the Way",options:["On the Way","Finished","Beginning","Success"]},
{word:"以上",correct:"More Than",options:["More Than","Less Than","Equal","Half"]},
{word:"以下",correct:"Less Than",options:["Less Than","More Than","Equal","Double"]}
],
 
N3:[
{word:"努力",correct:"Effort",options:["Effort","Accident","Travel","Friend"]},
{word:"協力",correct:"Cooperation",options:["Cooperation","Competition","Fight","Argument"]},
{word:"成長",correct:"Growth",options:["Growth","Loss","Damage","Fear"]},
{word:"環境",correct:"Environment",options:["Environment","Language","Money","Machine"]},
{word:"責任",correct:"Responsibility",options:["Responsibility","Freedom","Vacation","Dream"]},
{word:"期待",correct:"Expectation",options:["Expectation","Fear","Anger","Luck"]},
{word:"現状",correct:"Current Situation",options:["Current Situation","History","Future","Dream"]},
{word:"解決",correct:"Solution",options:["Solution","Problem","Mistake","Complaint"]},
{word:"判断",correct:"Judgment",options:["Judgment","Travel","Exercise","Repair"]},
{word:"提案",correct:"Proposal",options:["Proposal","Order","Punishment","Accident"]},
 
{word:"経験",correct:"Experience",options:["Experience","Food","Weather","Train"]},
{word:"準備",correct:"Preparation",options:["Preparation","Accident","Medicine","Music"]},
{word:"説明",correct:"Explanation",options:["Explanation","Question","Answer","Picture"]},
{word:"連絡",correct:"Contact",options:["Contact","Travel","Exercise","Repair"]},
{word:"必要",correct:"Necessary",options:["Necessary","Dangerous","Beautiful","Cold"]},
{word:"失敗",correct:"Failure",options:["Failure","Success","Victory","Reward"]},
{word:"案内",correct:"Guide",options:["Guide","Map","Food","Friend"]},
{word:"安全",correct:"Safety",options:["Safety","Danger","War","Accident"]},
{word:"習慣",correct:"Habit",options:["Habit","Festival","Game","Journey"]},
{word:"確認",correct:"Confirmation",options:["Confirmation","Cancellation","Attack","Escape"]},
 
{word:"参加",correct:"Participation",options:["Participation","Absence","Retirement","Escape"]},
{word:"選択",correct:"Choice",options:["Choice","Damage","Failure","Travel"]},
{word:"目的",correct:"Purpose",options:["Purpose","Accident","Problem","Delay"]},
{word:"原因",correct:"Cause",options:["Cause","Result","Reward","Gift"]},
{word:"結果",correct:"Result",options:["Result","Reason","Question","Answer"]},
{word:"可能",correct:"Possible",options:["Possible","Impossible","Dangerous","Expensive"]},
{word:"重要",correct:"Important",options:["Important","Unnecessary","Weak","Small"]},
{word:"危険",correct:"Danger",options:["Danger","Safety","Comfort","Peace"]},
{word:"利用",correct:"Use",options:["Use","Throw Away","Forget","Damage"]},
{word:"提供",correct:"Provide",options:["Provide","Reject","Hide","Destroy"]},
 
{word:"相談",correct:"Consultation",options:["Consultation","Argument","Punishment","Competition"]},
{word:"管理",correct:"Management",options:["Management","Destruction","Escape","Retirement"]},
{word:"評価",correct:"Evaluation",options:["Evaluation","Attack","Travel","Exercise"]},
{word:"対応",correct:"Response",options:["Response","Silence","Escape","Neglect"]},
{word:"処理",correct:"Processing",options:["Processing","Celebration","Shopping","Vacation"]},
{word:"実現",correct:"Realization",options:["Realization","Failure","Damage","Loss"]},
{word:"継続",correct:"Continuation",options:["Continuation","End","Failure","Destruction"]},
{word:"発見",correct:"Discovery",options:["Discovery","Loss","Damage","Failure"]},
{word:"到着",correct:"Arrival",options:["Arrival","Departure","Delay","Accident"]},
{word:"出発",correct:"Departure",options:["Departure","Arrival","Failure","Damage"]},
 
{word:"記録",correct:"Record",options:["Record","Problem","Question","Gift"]},
{word:"調査",correct:"Investigation",options:["Investigation","Celebration","Competition","Exercise"]},
{word:"増加",correct:"Increase",options:["Increase","Decrease","Loss","Damage"]},
{word:"減少",correct:"Decrease",options:["Decrease","Growth","Victory","Reward"]},
{word:"成功",correct:"Success",options:["Success","Failure","Damage","Loss"]},
{word:"要求",correct:"Demand",options:["Demand","Gift","Praise","Support"]},
{word:"活動",correct:"Activity",options:["Activity","Rest","Sleep","Vacation"]},
{word:"文化",correct:"Culture",options:["Culture","Machine","Money","Language"]},
{word:"技術",correct:"Technology",options:["Technology","History","Festival","Music"]},
{word:"知識",correct:"Knowledge",options:["Knowledge","Luck","Game","Exercise"]}
],
 
N2:[
{word:"改善",correct:"Improvement",options:["Improvement","Damage","Failure","Market"]},
{word:"維持",correct:"Maintain",options:["Maintain","Destroy","Exchange","Expand"]},
{word:"適応",correct:"Adaptation",options:["Adaptation","Education","Investigation","Separation"]},
{word:"削減",correct:"Reduction",options:["Reduction","Increase","Collection","Development"]},
{word:"慎重",correct:"Careful",options:["Careful","Angry","Lazy","Friendly"]},
{word:"優先",correct:"Priority",options:["Priority","Punishment","Accident","Reward"]},
{word:"達成",correct:"Achievement",options:["Achievement","Failure","Delay","Damage"]},
{word:"影響",correct:"Influence",options:["Influence","Choice","Profit","Responsibility"]},
{word:"貢献",correct:"Contribution",options:["Contribution","Competition","Prediction","Transportation"]},
{word:"対策",correct:"Countermeasure",options:["Countermeasure","Problem","Mistake","Travel"]},
 
{word:"状況",correct:"Situation",options:["Situation","Medicine","Machine","Festival"]},
{word:"増加",correct:"Increase",options:["Increase","Decrease","Damage","Loss"]},
{word:"減少",correct:"Decrease",options:["Decrease","Growth","Victory","Reward"]},
{word:"調査",correct:"Investigation",options:["Investigation","Celebration","Competition","Exercise"]},
{word:"確認",correct:"Confirmation",options:["Confirmation","Cancellation","Vacation","Attack"]},
{word:"経験",correct:"Experience",options:["Experience","Dream","Food","Journey"]},
{word:"責任",correct:"Responsibility",options:["Responsibility","Luck","Hobby","Game"]},
{word:"準備",correct:"Preparation",options:["Preparation","Accident","Failure","Escape"]},
{word:"発展",correct:"Development",options:["Development","Destruction","Decline","Loss"]},
{word:"解決",correct:"Solution",options:["Solution","Problem","Mistake","Damage"]},
 
{word:"提案",correct:"Proposal",options:["Proposal","Order","Punishment","Accident"]},
{word:"判断",correct:"Judgment",options:["Judgment","Travel","Repair","Exercise"]},
{word:"協力",correct:"Cooperation",options:["Cooperation","Conflict","Fight","Competition"]},
{word:"努力",correct:"Effort",options:["Effort","Vacation","Gift","Celebration"]},
{word:"成功",correct:"Success",options:["Success","Failure","Damage","Loss"]},
{word:"失敗",correct:"Failure",options:["Failure","Success","Achievement","Victory"]},
{word:"要求",correct:"Demand",options:["Demand","Gift","Praise","Support"]},
{word:"提供",correct:"Provide",options:["Provide","Destroy","Reject","Hide"]},
{word:"利用",correct:"Use",options:["Use","Throw Away","Damage","Forget"]},
{word:"参加",correct:"Participation",options:["Participation","Absence","Escape","Retirement"]},
 
{word:"連絡",correct:"Contact",options:["Contact","Journey","Exercise","Repair"]},
{word:"相談",correct:"Consultation",options:["Consultation","Argument","Punishment","Competition"]},
{word:"選択",correct:"Choice",options:["Choice","Damage","Failure","Travel"]},
{word:"結果",correct:"Result",options:["Result","Reason","Question","Answer"]},
{word:"目的",correct:"Purpose",options:["Purpose","Accident","Problem","Delay"]},
{word:"原因",correct:"Cause",options:["Cause","Result","Reward","Gift"]},
{word:"可能",correct:"Possible",options:["Possible","Impossible","Dangerous","Expensive"]},
{word:"重要",correct:"Important",options:["Important","Unnecessary","Weak","Small"]},
{word:"安全",correct:"Safety",options:["Safety","Danger","War","Accident"]},
{word:"危険",correct:"Danger",options:["Danger","Safety","Comfort","Peace"]},
 
{word:"期待",correct:"Expectation",options:["Expectation","Fear","Anger","Luck"]},
{word:"現状",correct:"Current Situation",options:["Current Situation","History","Future","Dream"]},
{word:"環境",correct:"Environment",options:["Environment","Language","Money","Machine"]},
{word:"成長",correct:"Growth",options:["Growth","Loss","Damage","Fear"]},
{word:"管理",correct:"Management",options:["Management","Destruction","Escape","Retirement"]},
{word:"評価",correct:"Evaluation",options:["Evaluation","Attack","Travel","Exercise"]},
{word:"処理",correct:"Processing",options:["Processing","Celebration","Vacation","Shopping"]},
{word:"対応",correct:"Response",options:["Response","Silence","Escape","Neglect"]},
{word:"実現",correct:"Realization",options:["Realization","Failure","Damage","Loss"]},
{word:"継続",correct:"Continuation",options:["Continuation","End","Failure","Destruction"]}
],
N1:[
{word:"概念",correct:"Concept",options:["Concept","Fruit","Animal","Money"]},
{word:"遂行",correct:"Accomplishment",options:["Accomplishment","Failure","Travel","Food"]},
{word:"懸念",correct:"Concern",options:["Concern","Happiness","Victory","Gift"]},
{word:"顕著",correct:"Remarkable",options:["Remarkable","Small","Cheap","Weak"]},
{word:"促進",correct:"Promotion",options:["Promotion","Delay","Destruction","Refusal"]},
{word:"妥当",correct:"Appropriate",options:["Appropriate","Dangerous","Expensive","Dirty"]},
{word:"把握",correct:"Understanding",options:["Understanding","Shopping","Exercise","Repair"]},
{word:"遂げる",correct:"Achieve",options:["Achieve","Lose","Borrow","Hide"]},
{word:"乏しい",correct:"Scarce",options:["Scarce","Plentiful","Beautiful","Famous"]},
{word:"緩和",correct:"Mitigation",options:["Mitigation","Increase","Attack","Competition"]},
 
{word:"脅威",correct:"Threat",options:["Threat","Success","Reward","Journey"]},
{word:"促す",correct:"Encourage",options:["Encourage","Prevent","Forget","Hide"]},
{word:"覆す",correct:"Overturn",options:["Overturn","Support","Create","Repair"]},
{word:"著しい",correct:"Significant",options:["Significant","Tiny","Weak","Rare"]},
{word:"逸脱",correct:"Deviation",options:["Deviation","Agreement","Victory","Growth"]},
{word:"抽象",correct:"Abstract",options:["Abstract","Concrete","Simple","Cheap"]},
{word:"兆候",correct:"Sign",options:["Sign","Problem","Accident","Promise"]},
{word:"阻害",correct:"Obstruction",options:["Obstruction","Support","Promotion","Growth"]},
{word:"妨げる",correct:"Hinder",options:["Hinder","Help","Build","Increase"]},
{word:"排除",correct:"Exclusion",options:["Exclusion","Acceptance","Support","Respect"]},
 
{word:"認識",correct:"Recognition",options:["Recognition","Travel","Game","Repair"]},
{word:"見解",correct:"Opinion",options:["Opinion","Map","Medicine","Food"]},
{word:"一貫",correct:"Consistency",options:["Consistency","Confusion","Damage","Luck"]},
{word:"懲戒",correct:"Discipline",options:["Discipline","Reward","Praise","Celebration"]},
{word:"審議",correct:"Deliberation",options:["Deliberation","Exercise","Shopping","Driving"]},
{word:"措置",correct:"Measure",options:["Measure","Mistake","Holiday","Dream"]},
{word:"譲歩",correct:"Concession",options:["Concession","Victory","Demand","Attack"]},
{word:"潜在",correct:"Potential",options:["Potential","Reality","Failure","Danger"]},
{word:"脆弱",correct:"Fragile",options:["Fragile","Strong","Rich","Fast"]},
{word:"執行",correct:"Execution",options:["Execution","Discussion","Journey","Choice"]},
 
{word:"是正",correct:"Correction",options:["Correction","Expansion","Celebration","Profit"]},
{word:"網羅",correct:"Coverage",options:["Coverage","Reduction","Loss","Damage"]},
{word:"顧慮",correct:"Consideration",options:["Consideration","Neglect","Attack","Escape"]},
{word:"介入",correct:"Intervention",options:["Intervention","Observation","Vacation","Exercise"]},
{word:"遂行力",correct:"Execution Ability",options:["Execution Ability","Language Skill","Memory","Speed"]},
{word:"漠然",correct:"Vague",options:["Vague","Clear","Strong","Certain"]},
{word:"堅実",correct:"Steady",options:["Steady","Risky","Weak","Poor"]},
{word:"懐疑",correct:"Doubt",options:["Doubt","Trust","Hope","Joy"]},
{word:"履行",correct:"Fulfillment",options:["Fulfillment","Cancellation","Failure","Loss"]},
{word:"断念",correct:"Abandonment",options:["Abandonment","Success","Promotion","Victory"]},
 
{word:"見落とす",correct:"Overlook",options:["Overlook","Discover","Repair","Collect"]},
{word:"著者",correct:"Author",options:["Author","Doctor","Teacher","Pilot"]},
{word:"奨励",correct:"Encouragement",options:["Encouragement","Punishment","Restriction","Ban"]},
{word:"配慮",correct:"Consideration",options:["Consideration","Neglect","Attack","Conflict"]},
{word:"慣行",correct:"Custom",options:["Custom","Law","Accident","Rule"]},
{word:"懸命",correct:"Earnest",options:["Earnest","Lazy","Weak","Slow"]},
{word:"打開",correct:"Breakthrough",options:["Breakthrough","Failure","Damage","Loss"]},
{word:"模索",correct:"Search",options:["Search","Destruction","Attack","Escape"]},
{word:"推奨",correct:"Recommendation",options:["Recommendation","Refusal","Ban","Restriction"]},
{word:"抽出",correct:"Extraction",options:["Extraction","Addition","Expansion","Damage"]}
]
 
};
 
let questions = [];
let current = 0;
let score = 0;
let timer;
let timeLeft = 15;
 
function startGame(level){
 
document.getElementById("menu").style.display="none";
document.getElementById("game").style.display="block";
 
questions = [...data[level]]
.sort(() => Math.random() - 0.5)
.slice(0,20);
 
current = 0;
score = 0;
 
loadQuestion();
}
function loadQuestion(){
 
if(current >= questions.length){
 
document.getElementById("game").innerHTML = `
<h1>🏆 Game Over</h1>
<h2>Score : ${score}</h2>
<button onclick="location.reload()">
Play Again
</button>
`;
 
return;
}
 
let q = questions[current];
 
document.getElementById("word").innerHTML = q.word;
 
let html = "";
 
// Answer positions random
let shuffledOptions = [...q.options]
.sort(() => Math.random() - 0.5);
 
shuffledOptions.forEach(option => {
 
html += `
<button class="answer-btn"
onclick="checkAnswer(this,'${option}')">
${option}
</button>
`;
 
});
function startTimer(){
 
    clearInterval(timer);
 
    timeLeft = 15;
 
    document.getElementById("timer").innerText = timeLeft;
 
    timer = setInterval(() => {
 
        timeLeft--;
 
        document.getElementById("timer").innerText = timeLeft;
 
        if(timeLeft <= 0){
 
            clearInterval(timer);
 
            current++;
 
            // Progress Bar တက်အောင်
            let percent = (current / questions.length) * 100;
 
            document.getElementById("progress").style.width =
            percent + "%";
 
            loadQuestion();
        }
 
    },1000);
 
}
 
document.getElementById("answers").innerHTML = html;
 
document.getElementById("score").innerHTML = score;
 
startTimer();
}
 
function checkAnswer(btn, answer){
 
    clearInterval(timer);
 
    let correct = questions[current].correct;
 
    if(answer === correct){
 
        score += 10;
 
        btn.classList.add("correct");
 
        document.getElementById("resultMessage").innerHTML =
        "✅ Correct!";
 
    }else{
 
        btn.classList.add("wrong");
 
        document.getElementById("resultMessage").innerHTML =
        "❌ Wrong!<br>✔ Right Answer: " + correct;
    }
 
    document.getElementById("score").innerText = score;
 
   setTimeout(()=>{
 
    document.getElementById("resultMessage").innerHTML = "";
 
    current++;
 
    // Progress Bar Update
    let percent = (current / questions.length) * 100;
 
    document.getElementById("progress").style.width =
    percent + "%";
 
    loadQuestion();
 
},2000);
 
}
 
</script>
 
</body>
</html>