@php
    use App\User;

    if(!isset($_GET['id'])) return;
    $id = $_GET['id'];

    $user = User::where('id', $id)->first();
    if($user == false || $user == null) return;

    $owner = !Auth::guest() && $id == Auth::user()->id;
@endphp

<div class="row">
    <div class="col-xs-12">
        <div class="user_block">
            <div class="user-info">
                @if($owner)
                    <div class="user-info-tab user-logout" onclick="window.location.href = '/logout'">
                        Выйти
                    </div>
                @endif

                <div class="user-avatar">
                    <img alt="" data-src="{{$user->avatar}}" class="lazyload">
                    @if($owner)
                        <div class="avatar-edit" onclick="$('#avatar-file').click()"><i class="fas fa-camera"></i></div>
                        <form id="avatar-form" action enctype="multipart/form-data" method="post" style="display: none">
                            <input id="avatar-file" name="pictures" onchange="$('#avatar-form').submit()" type="file" accept="image/*">
                            {{ csrf_field() }}
                        </form>
                    @endif
                </div>

                <div class="user-info-block">
                    <p>{{$user->username}}</p>
                    <strong class="level-{{$user->level}}"  @if($owner) onclick="setTab('level')" @endif>{{$user->level}} уровень</strong>
                    @if($owner && $user->level != 10)
                        <div class="user-level-progress">
                            <div class="level-bg-{{$user->level}}" style="width: {{($user->exp/\App\User::getRequiredExperience($user->level + 1))*100}}%"></div>
                        </div>
                    @endif

                    <div class="user-info-tabs">
                        <div class="user-info-tab" data-tab="history" onclick="setTab('history')">
                            История
                        </div>
                        <div class="user-info-tab" data-tab="achievements" onclick="setTab('achievements')">
                            Достижения
                        </div>
                        @if($owner)
                            <div class="user-info-tab" data-tab="in" onclick="setTab('in')">
                                Пополнения
                            </div>
                            <div class="user-info-tab" data-tab="out" onclick="setTab('out')">
                                Выплаты
                            </div>
                            <div class="user-info-tab" data-tab="level" onclick="setTab('level')">
                                Уровень
                            </div>
                            <div class="user-info-tab" data-tab="ref" onclick="setTab('ref')">
                                Партнерская программа
                            </div>
							 <div class="user-info-tab" data-tab="settings" onclick="setTab('settings')">
                                Настройки
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="user_block user_main">
            <div class="user_tab user_live_table_tab" id="history">
                @php
                    $drops = \App\Http\Controllers\GeneralController::user_drops($id, 0);
                @endphp
                @if(count($drops) == 0)
                    <div class="user_tab_empty">
                        <i class="fad fa-clock"></i>
                        <p>Здесь ничего нет</p>
                    </div>
                @else
                <table class="live_table" id="user_drops">
                    <thead>
                    <tr class="live_table-header">
                        <th>ИГРА</th>
                        <th class="hidden-xs">ВРЕМЯ</th>
                        <th class="hidden-xs">СТАВКА</th>
                        <th class="hidden-xs">КОЭФФ.</th>
                        <th>ВЫИГРЫШ</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($drops as $d)
                            <tr class="live_table-game">
                                <th>
                                    <div class="ll_icon hidden-xs hidden-sm" onclick="load('/{{strtolower($d['name'])}}')">
                                        <i class="{{$d['icon']}}"></i>
                                    </div>
                                    <div class="ll_game">
                                        <span onclick="load('/{{$d['game_id'] == 12 ? 'cases' : strtolower($d['name'])}}')">{{$d['name']}}</span>
                                        @if($d['game_id'] == 12)
                                            <p onclick="load('/cases')">Перейти</p>
                                        @else
                                            <p onclick="user_game_info({{$d['id']}})">Просмотр</p>
                                        @endif
                                    </div>
                                </th>
                                <th class="hidden-xs">{{$d['time']}}</th>
                                <th class="hidden-xs">@if($d['user_id'] != -2) {{$d['bet']}} &nbsp;<i class="fad fa-coins"></i> @endif</th>
                                <th class="hidden-xs">@if($d['user_id'] != -2 && $d['game_id'] != 12) x{{$d['mul']}} @endif @if($d['game_id'] == 12) — @endif</th>
                                <th>@if($d['amount'] > 0)+@endif{{$d['amount']}} &nbsp;<i class="fad fa-coins"></i></th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            </div>
            <div class="user_tab" style="padding: 25px;" id="achievements">
                <div class="col-xs-12 col-sm-12 col-md-2 mobile-ach-tabs">
                    <div class="ach-scroll-content">
                        <div class="nano">
                            <div class="nano-content">
                                <div class="ach-menu">
                                    @php
                                        $sub = function($category) {
                                            return '<div class="ach-menu-element ach-submenu" id="'.$category.'">'
                                            .'<div onclick="filter(\'all\')">Все</div>'
                                            .'<div onclick="filter(\'bronze\')" style="margin-top: 13px"><i class="fad fa-award bronze"></i> Бронза</div>'
                                            .'<div onclick="filter(\'silver\')"><i class="fad fa-award silver"></i> Серебро</div>'
                                            .'<div onclick="filter(\'gold\')"><i class="fad fa-award gold"></i> Золото</div>'
                                            .'<div onclick="filter(\'platinum\')"><i class="fad fa-award platinum"></i> Платина</div></div>';
                                        };

                                        $translate = function($category) {
                                            switch($category) {
                                                case 'user': return 'Пользователь';
                                                case 'mines': return 'Mines';
                                                case 'stairs': return 'Stairs';
                                                case 'tower': return 'Tower';
                                                case 'blackjack': return 'Blackjack';
                                                case 'roulette': return 'Roulette';
                                                case 'dice': return 'Dice';
                                                case 'coinflip': return 'Coinflip';
                                                case 'wheel': return 'Wheel';
                                                case 'hilo': return 'HiLo';
                                                case 'crash': return 'Crash';
                                                case 'keno': return 'Keno';
                                                case 'event': return 'События';
                                                default: return $category;
                                            }
                                        };
                                    @endphp

                                    <div class="ach-menu-element ach-menu-active" onclick="loadAchievements('all')">
                                        Все достижения
                                    </div>
                                    <div class="ach-menu-sep"></div>

                                    @foreach(\App\Achievements::categories() as $category)
                                        <div class="ach-menu-element" data-submenu="{{$category}}" onclick="loadAchievements('{{$category}}')">
                                            {{ $translate($category) }}
                                            <i class="fas fa-angle-right"></i>
                                        </div>
                                        {!! $sub($category) !!}
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-10">
                    <div id="load" class="profile-loader" style="display: none">
                        <div></div>
                    </div>
                    <div class="ach-scroll-content">
                        <div class="nano">
                            <div class="nano-content" id="achievements_content"></div>
                        </div>
                    </div>
                </div>
            </div>
            @if($owner)
                <div class="user_tab user_live_table_tab" id="in">
                    @php
                        $drops = DB::table('payments')->where('user', Auth::user()->id)->orderBy('id', 'desc')->get();
                    @endphp
                    @if(sizeof($drops) == 0)
                        <div class="user_tab_empty">
                            <i class="fad fa-clock"></i>
                            <p>Здесь ничего нет</p>
                        </div>
                    @else
                        <table class="live_table">
                            <thead>
                                <tr class="live_table-header">
                                    <th>#</th>
                                    <th class="hidden-xs">Наименование</th>
                                    <th>Сумма</th>
                                    <th class="hidden-xs">Дата</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($drops as $d)
                                    <tr class="live_table-game">
                                        <th>
                                            <div class="ll_game">
                                                <span>{{$d->id  + 1871342}}</span>
                                            </div>
                                        </th>
                                        <th class="hidden-xs">Пополнение баланса на {{$d->amount}} руб</th>
                                        <th>{{$d->amount}} руб</th>
                                        <th class="hidden-xs">{{$d->created_at}}</th>
                                        <th>
                                            @if($d->status == 0)
                                                Ожидание
											@endif
                                            @if($d->status == 1)
                                                Успешно
											@endif
											@if($d->status == 2)
                                                Ошибка
                                            @endif
                                        </th>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="user_tab user_live_table_tab" id="out">
                    @php
                        $drops = DB::table('withdraw')->where('user_id', Auth::user()->id)->orderBy('id', 'desc')->get();
                    @endphp
                    @if(sizeof($drops) == 0)
                        <div class="user_tab_empty">
                            <i class="fad fa-clock"></i>
                            <p>Здесь ничего нет</p>
                        </div>
                    @else
																							<style>
					@media (max-width: 780px) {
						.live_table-game1 th, .live_table-header1 th {
    padding: 10px;
    font-size: 9.5px;
    color: #a8a8a8;
}
					}
					@media (min-width: 780px) {
						.live_table-game1 th, .live_table-header1 th {
    padding: 10px;
    font-size: 14px;
    color: #a8a8a8;
}
					}
					.live_table tr:last-child {
    border: unset!important;
}
.live_table tr {
    border-bottom: 1px solid hsla(0,0%,100%,.05);
}
.live_table-header1 th {
    border-top: 1px solid hsla(0,0%,100%,.1);
    border-bottom: 1px solid hsla(0,0%,100%,.1);
}
					</style>
                        <table class="live_table">
                            <thead>
                            <tr class="live_table-header1">
                                <th>#</th>
                                <th class="hidden-xs">Наименование</th>
                                <th>Сумма</th>
                                <th class="hidden-xs">Дата</th>
                                <th class="hidden-lg hidden-sm hidden-md">Кошелек</th>
                                <th>Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($drops as $d)
                                    <tr class="live_table-game1">
                                        <th>
                                            <div class="ll_game">
                                                <span>{{$d->id}}</span>
                                            </div>
                                        </th>
                                        <th class="hidden-xs">
                                            Выплата на сумму {{$d->amount}} руб
                                            @if(Auth::user()->deposit == 0)
                                                <i class="fas fa-exclamation-triangle extendedPayout tooltip" title="Срок выплаты может быть увеличен до 2 недель, так как Вы играли на бесплатный баланс."></i>
                                            @endif
                                            <br>
                                            <span style="color: white">Кошелек: {{ $d->wallet }}</span>
                                        </th>
                                        <th>{{ $d->amount }} руб</th>
                                        <th class="hidden-xs">{{$d->created_at}}</th>
                                        <th class="hidden-lg hidden-sm hidden-md">
                                            {{ $d->wallet }}
                                        </th>
                                        <th>
                                            @if($d->status == 0)
                                                Ожидание
                                                <br>
                                                <a class="ll" onclick="cancelWithdraw({{$d->id}})" href="javascript:void(0)">Отменить</a>
                                            @elseif($d->status == 1)
                                                Успешно
                                            @elseif($d->status == 2)
                                                Отказано
                                            @elseif($d->status == 3)
                                                Отменено
                                            @elseif($d->status == 4)
                                                Ожидание
                                            @endif
                                        </th>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="user_tab" id="level">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="levels-table">
                                @for($i = 1; $i <= 10; $i++)
                                    <div class="level">
                                        <div class="level-name level-{{$i}}">Уровень {{$i}}</div>
                                        @if($i == Auth::user()->level) <div class="level-desc level-{{$i}}"><i class="fal fa-check"></i> Это ваш уровень</div>
                                        @elseif($i == Auth::user()->level + 1) <div class="level-desc level-{{$i}}">Количество опыта: {{Auth::user()->exp}}/{{\App\User::getRequiredExperience($i)}}</div>
                                        @elseif($i < Auth::user()->level) <div class="level-desc level-{{$i}}"><i class="fal fa-check"></i> Вы получили этот уровень</div> @endif

                                        @if($i > 1 && $i >= Auth::user()->level) <div class="level-desc @if($i == Auth::user()->level) level-4 @else level-1 @endif">Дополнительный бонус: +{{\App\User::getBonusModifier($i)}} руб</div> @endif
                                        @if($i == 10) <div class="level-desc @if($user->level < 10) level-1 @else level-10 @endif">Золотая рамка для сообщения в чате</div> @endif
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-9">
                            <div class="user-exp">
                                Ваш уровень:
                                <span>{{$user->level}}</span>
                            </div>
                            <div class="user-exp">
                                Дополнительный бонус:
                                <span>
                                    @if($user->level > 1)
                                        {{\App\User::getBonusModifier($user->level)}} руб
                                    @else
                                        Нет
                                    @endif
                                </span>
                            </div>
                            @if($user->level < 10)
                                <div class="user-exp" style="margin-top: 15px">
                                    Опыт до {{$user->level + 1}} уровня:
                                    <span>{{$user->exp}}/{{\App\User::getRequiredExperience($user->level + 1)}} ({{intval(($user->exp/\App\User::getRequiredExperience($user->level + 1))*100)}}%)</span>
                                </div>
                                <div class="user-level-progress-big">
                                    <div class="level-bg-{{$user->level + 1}}" style="width: {{($user->exp/\App\User::getRequiredExperience($user->level + 1))*100}}%"></div>
                                </div>
                            @endif
                            <div class="faq">
                                <div class="faq-block">
                                    <div class="faq-header faq-header-active">
                                        Что такое уровень?
                                    </div>
                                    <div class="faq-content" style="display: block">
                                        Уровень - это награда за продолжительное участие в активностях на сайте.
                                    </div>
                                </div>
                                <div class="faq-block">
                                    <div class="faq-header">
                                        Что дает уровень?
                                    </div>
                                    <div class="faq-content">
                                        Уровень дает:
                                        <ul>
                                            <li>
                                                1. Дополнительный бонус для колеса на странице <a href="javascript:void(0)" onclick="load('/bonus')" class="ll">Бонус</a>.
                                            </li>
                                            <li>
                                                2. Значок в чате, показывающий Ваш уровень на сайте.
                                            </li>
                                            <li>
                                                3. 10 уровень выделяет сообщение в чате золотым цветом.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="faq-block">
                                    <div class="faq-header">
                                        Что такое дополнительный бонус?
                                    </div>
                                    <div class="faq-content">
                                        Дополнительный бонус - это сумма, которая гарантированно добавится на Ваш счет после прокрутки колеса на странице <a href="javascript:void(0)" onclick="load('/bonus')" class="ll">Бонус</a>
                                    </div>
                                </div>
                                @if($user->level < 10)
                                    <div class="faq-block">
                                        <div class="faq-header">
                                            Как получить опыт?
                                        </div>
                                        <div class="faq-content">
                                            <ul>
                                                <li>
                                                    1. Получая бесплатный бонус<hr>
                                                    - Каждое получение бонуса добавляет 35 единиц опыта на Ваш аккаунт.
                                                </li>
                                                <li>
                                                    2. Получая достижения<hr>
                                                    - Бронзовое достижение добавляет 1.5% опыта.<br>
                                                    - Серебрянное достижение добавляет 5% опыта.<br>
                                                    - Золотое достижение добавляет 10% опыта.<br>
                                                    - Платиновое достижение добавляет 25% опыта.<br>
                                                    <br>
                                                    <a href="javascript:void(0)" onclick="setTab('achievements')" class="ll">Узнать подробнее</a>
                                                </li>
                                                <li>
                                                    3. Пополняя счет<hr>
                                                    - Каждые 10 руб повышают Ваш опыт на 10%.
                                                    @if($user->level < 3)
                                                    <br>
                                                    - Первое пополнение счета повышает Ваш уровень до 3.
                                                    @endif
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
               <div class="user_tab" id="ref">
                    <div class="col-xs-12 col-sm-12 col-md-9">
                        <div class="ref-header">Партнерская программа</div>
                        <div class="ref-content">
                            Приглашайте своих друзей и вместе зарабатывайте бонусы!<br>
                            Каждый человек, который зарегистрируется по вашему реферальному коду получит {{$settings->promo_sum}} руб

                            <br><br>
                            В награду за каждого <span class="ref_bb">активного реферала</span> <i class="fas fa-question-circle fqc tooltip" title="Активным рефералом считается тот, у кого общая сумма выигрыша всех игр достигла 50 руб"></i> вы получаете {{$settings->promo_sum}} руб,<br>
                            а каждые 10 активных рефералов позволяют прокрутить колесо с денежной наградой.<br><br>

                            <span>Ваша ссылка для приглашения <i class="fas fa-question-circle fqc tooltip" title="Перейдя по этой ссылке и после регистрации пользователь автоматически становится Вашим рефералом."></i>:</span>
                            <div class="ref_link tooltip copy" title="Нажмите, чтобы скопировать">https://{{$_SERVER['SERVER_NAME']}}/ref/{{Auth::user()->ref_code}}</div>
                            <span>Ваш реферальный промокод:</span>
                            <div class="ref_promo tooltip copy" title="Нажмите, чтобы скопировать">{{Auth::user()->ref_code}}</div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-3">
                        <div class="ref-header">Приглашенные рефералы</div>
                        <div id="ref_content">Загрузка...</div>
                    </div>
                </div>
				<!--- Settings --->
				<div class="user_tab" id="settings">
                    <div class="col-xs-12 col-sm-12 col-md-9">
                        <div class="ref-header">Основные</div>
                        <div class="ref-content">
						<div class="fn_form_block_h1">
                <p>Ваш UID</p>
                <input value="{{\App\User::where('id', Auth::user()->id)->first()->uid}}" disabled id="_client_id" placeholder="uid">
            </div>
						<div class="fn_form_block">
                <p>Имя</p>
                <input value="{{\App\User::where('id', Auth::user()->id)->first()->username}}" disabled id="_client_name" placeholder="Username">
                <a class="ll cs_change_settings" onclick="client_username_change_prompt()">Изменить</a>
            </div>
			<br>
<div class="fn_form_block">
                <p>Почта</p>
                <input value="{{\App\User::where('id', Auth::user()->id)->first()->email}}" disabled id="_client_email" placeholder="Email">
                <a class="ll cs_change_settings" onclick="client_email_change_prompt()">Изменить</a>
            </div>
            </div>
			<br><br>
			 <div class="ref-header">Безопасность</div>
			 <div class="ref-content">
			 @if(Auth::user()->password == null)
				<!-- login pass null --->
				<div class="login_fields auth-tab-active" data-auth-action="set_pass">
                <div class="login_fields__user">
                    <div class="icon user-icon">
                        <img src="/img/lock_icon_copy.png" alt="">
                    </div>
                    <input id="pass1" placeholder="Новый пароль" type="password">
                    <div class="validation">
                        <img src="/img/tick.png" alt="">
                    </div>
                </div>
                <div class="login_fields__password">
                    <div class="icon password-icon">
                        <img src="/img/lock_icon_copy.png" alt="">
                    </div>
                    <input id="pass2" placeholder="Повторите пароль" type="password">
                    <div class="validation">
                        <img src="/img/tick.png" alt="">
                    </div>
                </div>
				<br>
                <div class="login_fields__submit">
                    <input type="submit" id="p_s_n" value="Сохранить">
                </div>
				<br><br>
            </div>
			<!-- end login pass null --->
			@endif
			@if(Auth::user()->password != null)
				<!-- login pass yes --->
				<div class="login_fields" data-auth-action="change_pass">
                <div class="login_fields__user" id="oldpassword" style="display: block;">
                    <div class="icon password-icon">
                        <img src="/img/lock_icon_copy.png" alt="">
                    </div>
                    <input id="oldpass" placeholder="Текущий пароль" type="password">
                    <div class="validation">
                        <img src="/img/tick.png" alt="">
                    </div>
                    <i class="fas fa-info-circle register-email-info tooltip" title="Текущий пароль, использованный для авторизации."></i>
                </div>
                <div class="login_fields__user">
                    <div class="icon user-icon">
                        <img src="/img/lock_icon_copy.png" alt="">
                    </div>
                    <input id="pass1" placeholder="Новый пароль" type="password">
                    <div class="validation">
                        <img src="/img/tick.png" alt="">
                    </div>
                </div>
                <div class="login_fields__password">
                    <div class="icon password-icon">
                        <img src="/img/lock_icon_copy.png" alt="">
                    </div>
                    <input id="pass2" placeholder="Повторите пароль" type="password">
                    <div class="validation">
                        <img src="/img/tick.png" alt="">
                    </div>
                </div>
				<br><br>
                <div class="login_fields__submit">
                    <input type="submit" id="p_s_n" value="Сохранить">
                </div>
            </div>
			<!-- end login pass yes --->
			@endif
            </div>
			<br><br>
<br><br>
										 <div class="ref-header">Честная игра</div>
										 <div class="ref-content">
                            <div class="fn_form_block">
                <p>Клиентский хэш</p>
                <input value="{{\App\User::where('id', Auth::user()->id)->first()->client_seed}}" disabled id="_client_hash" placeholder="Hash">
                <a class="ll cs_change_settings" onclick="client_seed_change_prompt_user()">Изменить</a>
            </div> </div>
                        </div>
                    </div>
                </div>
				<!--- Settings --->
            @endif
        </div>
    </div>
</div>
</div>
</div>
</div>
  </div>
</div>
