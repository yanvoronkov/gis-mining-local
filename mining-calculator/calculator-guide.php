<section class="calculator-guide">
  <div class="container calculator-guide__inner">

    <h2 class="calculator-guide__title">
      Как пользоваться калькулятором
    </h2>

    <div class="calculator-guide__layout">

      <!-- Шаги -->
      <ul class="calculator-guide__steps">
        <li class="is-active" data-step="1">
          <span>1</span>
          Выбор модели асика
        </li>
        <li data-step="2">
          <span>2</span>
          Быстрый поиск
        </li>
        <li data-step="3">
          <span>3</span>
          Мгновенный расчёт
        </li>
        <li data-step="4">
          <span>4</span>
          Прогноз по курсу
        </li>
        <li data-step="5">
          <span>5</span>
          Стоимость электроэнергии
        </li>
        <li data-step="6">
          <span>6</span>
          Период и валюта
        </li>
      </ul>

      <!-- Контент -->
      <div class="calculator-guide__content">

        <div class="calculator-guide__item is-active" data-step="1">
          <p>
            Выбираем модель асика. После выбора все данные по его стоимости и энергопотреблению загрузятся автоматически.
          </p>
          <img src="/mining-calculator/img/1calc.jpg" alt="">
        </div>

        <div class="calculator-guide__item" data-step="2">
          <p>
            Для быстрого выбора асика можете ввести его название или хешрейт в поиске.
          </p>
          <img src="/mining-calculator/img/2calc.jpg" alt="">
        </div>

        <div class="calculator-guide__item" data-step="3">
          <p>
            В ту же секунду вы получите результат расчета. Ваша чистая прибыль и % годовых. Все посчитается по актуальному курсу на день расчета. 
          </p>
          <img src="/mining-calculator/img/3calc.jpg" alt="">
        </div>

        <div class="calculator-guide__item" data-step="4">
          <p>
           А если вы захотите сделать прогноз на повышении или понижении курса валют, вы можете выбрать нужные вам значения, рассчитав доходность по своему курсу.
          </p>
          <img src="/mining-calculator/img/4calc.jpg" alt="">
        </div>

        <div class="calculator-guide__item" data-step="5">
          <p>
           Здесь же вы можете установить стоимость за 1 кВт электроэнергии, данные по расходам поменяются в реальном времени.
          </p>
          <img src="/mining-calculator/img/5calc.jpg" alt="">
        </div>

        <div class="calculator-guide__item" data-step="6">
          <p>
            В результатах расчета вы можете выбрать данные за день, месяц или за год. 
А так же доходность в разрезе BTC, $ или рубля 
          </p>
          <img src="/mining-calculator/img/6calc.jpg" alt="">
        </div>

      </div>
    </div>
  </div>
</section>



<style>
   .page-about .calculator-guide {
  padding: 80px 0;
}

.page-about .calculator-guide__title {
  text-align: center;
  margin-bottom: 40px;
  font-family: "DrukTextWideCyr-Medium", sans-serif;
  font-size: 180%;
}

/* Сетка */
.page-about .calculator-guide__layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 40px;
}

/* Список шагов */
.page-about .calculator-guide__steps {
  list-style: none;
  padding: 0;
  margin: 0;
}

.page-about .calculator-guide__steps li {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 14px 16px;
  border-radius: 12px;
  cursor: pointer;
  color: #4b5563;
  transition: background .2s ease, color .2s ease;
}

.page-about .calculator-guide__steps li span {
  width: 28px;
  height: 28px;
  background: #e0e2ff;
  color: #5b61ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  transition: background .2s ease, color .2s ease;
}

/* Активный шаг */
.page-about .calculator-guide__steps li.is-active {
  background: #5b61ff;
  color: #fff;
}

.page-about .calculator-guide__steps li.is-active span {
  background: rgba(255,255,255,.25);
  color: #fff;
}

/* Контент */
.page-about .calculator-guide__item {
  display: none;
}

.page-about .calculator-guide__item.is-active {
  display: block;
}

.page-about .calculator-guide__item p {
  font-size: 16px;
  color: #374151;
}

.page-about .calculator-guide__item img {
  margin-top: 16px;
  width: 100%;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0,0,0,.08);
}

/* Мобилка */
@media (max-width: 900px) {
  .page-about .calculator-guide__layout {
    grid-template-columns: 1fr;
  }

  .page-about .calculator-guide__steps {
    display: flex;
    gap: 8px;
    overflow-x: auto;
  }

  .page-about .calculator-guide__steps li {
    white-space: nowrap;
  }
}


</style>


<script>
document.querySelectorAll('.calculator-guide__steps li').forEach(step => {
  step.addEventListener('click', () => {
    const id = step.dataset.step;

    document.querySelectorAll('.calculator-guide__steps li')
      .forEach(s => s.classList.remove('is-active'));

    document.querySelectorAll('.calculator-guide__item')
      .forEach(i => i.classList.remove('is-active'));

    step.classList.add('is-active');
    document.querySelector(`.calculator-guide__item[data-step="${id}"]`)
      .classList.add('is-active');
  });
});
</script>
