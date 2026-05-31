<?php include '__header__.tpl.php'; ?>

<section>
  <header>
    <h2 class="heading">Game #<?php echo $this->game_details['game_id']; ?> Summary</h2>
    <div class="headinglink"> ( <a href="game_log.php?game_id=<?php echo $this->game_details['game_id'] ?>">show game log</a> | <a href="<?php echo htmlspecialchars($this->include_bots_toggle_url, ENT_QUOTES); ?>"><?php echo $this->include_bots ? 'hide bots' : 'include bots'; ?></a> )</div>
  </header>

  <table>
    <colgroup>
      <col class="levelshot" />
      <col class="item" />
      <col />
    </colgroup>

    <thead>
      <tr><th colspan="3">Game info</th></tr>
    </thead>

    <tbody>
     <tr>
      <td rowspan="10" class="levelshot">
        <img class="levelshot" alt="<?php echo htmlspecialchars($this->map['game_map_name'],ENT_QUOTES); ?>" src="_levelshot.php?map_id=<?php echo ($this->game_details['game_map_id']); ?>" />
      </td>
      <td><strong>Map Name</strong></td>
      <td><strong><a href="map_details.php?map_id=<?php echo $this->game_details['game_map_id'] ; ?>"><?php echo replace_color_codes($this->map['game_map_name']); ?></a></strong></td>
     </tr>
     <tr>
      <td>Winner</td>
      <td><?php echo $this->game_details['game_winner']; ?></td>
     </tr>
     <tr>
      <td>Game time</td>
      <td><?php echo $this->game_details['game_length']; ?></td>
     </tr>
     <tr>
      <td>Date <small>(UTC)</small></td>
      <td><?php echo $this->game_details['game_timestamp']; ?></td>
     </tr>
    </tbody>
  </table>
<?php $tables = array(array('Aliens', 'alien'), array('Humans', 'human'));
      foreach ($tables as $table)
      {
?>
  <table>
    <colgroup>
      <col class="playername" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
    </colgroup>

    <thead>
      <tr class="<?php echo $table[1]; ?>s-teamshader">
        <th><?php echo $table[0]; ?></th>
        <th>Score</th>
        <th>Kills</th>
        <th>Assists</th>
        <th>Team Kills</th>
        <th>Enemy Assists</th>
        <th>Deaths</th>
        <th>Time</th>
      </tr>
    </thead>

    <tbody>
      <?php $count = false; $time = 'time_'.$table[1];
            foreach ($this->players as $player) { ?>
        <?php if ($player[$time]) { ?>
      <tr class="list" >
        <td class="playername"><?php echo player_link($player['player_id'], $player['player_name']) ?><?php if (!empty($player['player_is_bot'])): ?> <span class="bot">bot</span><?php endif; ?></td>
        <td><?php echo $player['stats_score'] ?></td>
        <td><?php echo $player['stats_kills'] ?></td>
        <td><?php echo $player['stats_assists'] ?></td>
        <td><?php echo $player['stats_teamkills'] ?></td>
        <td><?php echo $player['stats_enemyassists'] ?></td>
        <td><?php echo $player['stats_deaths'] ?></td>
        <td><?php echo $player[$time] ?></td>
      </tr>
        <?php   $count = true;
              }
            }
            if (!$count) { ?>
        <tr class="emptylist">
          <td colspan="8">No players</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
<?php } ?>

  <table>
    <colgroup>
      <col class="playername" />
      <col class="data" /><col class="data" /><col class="data" /><col class="data" /><!-- dummy columns for width calculation -->
      <col class="data" />
    </colgroup>

    <thead>
      <tr class="spectators-teamshader">
        <th colspan="5">Spectators</th>
        <th>Time</th>
      </tr>
    </thead>

    <tbody>
      <?php $count = false; foreach ($this->players as $player) { ?>
        <?php if ($player['time_spec'] && !$player['time_human'] && !$player['time_alien']) { ?>
      <tr class="list">
        <td class="playername" colspan="5"><?php echo player_link($player['player_id'], $player['player_name']) ?><?php if (!empty($player['player_is_bot'])): ?> <span class="bot">bot</span><?php endif; ?></td>
        <td><?php echo $player['time_spec'] ?></td>
      </tr>
        <?php   $count = true;
              }
            }
            if (!$count) { ?>
        <tr class="emptylist">
          <td colspan="6">None</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col />
    </colgroup>

    <thead>
      <tr>
        <th>Stats per minute</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td><?php graph_killsInGame($this->game_details['game_id'], 'Nothing of interest… short game…', 'Wow, like really peaceful, man!'); ?></td>
      </tr>
    </tbody>
  </table>

  <?php if (!empty($this->gameplay_stats) && (!empty($this->gameplay_stats['samples']) || !empty($this->gameplay_stats['events']))): ?>
  <section class="gameplay-stats-panel">
    <header>
      <h2 class="heading">Detailed Gameplay Stats</h2>
      <div class="headinglink">Match ID: <?php echo htmlspecialchars($this->gameplay_stats['match_id'], ENT_QUOTES); ?></div>
    </header>

    <?php if (!$this->include_bots): ?>
    <div class="privacy">( Gameplay stats are rendered from the raw match file and may still include bot-driven events. )</div>
    <?php endif; ?>

    <div class="gameplay-stats-ui" id="gameplay-stats-ui">
      <div class="gameplay-stats-summary" id="gameplay-stats-summary"></div>
      <div class="gameplay-stats-legend" id="gameplay-stats-series-legend"></div>
      <div class="gameplay-stats-legend" id="gameplay-stats-legend"></div>
      <div class="gameplay-stats-controls" id="gameplay-stats-controls"></div>
      <div class="gameplay-stats-chart-shell" id="gameplay-stats-shell">
        <svg id="gameplay-stats-chart" viewBox="0 0 1280 760" aria-label="Gameplay stats chart"></svg>
        <div class="gameplay-stats-tooltip" id="gameplay-stats-tooltip"></div>
      </div>
      <div class="gameplay-stats-footer">Hover the chart for sampled values. Hover event markers for purchase details.</div>
    </div>
  </section>

  <style>
    .gameplay-stats-panel { margin-top: 1.5rem; }
    .gameplay-stats-ui {
      --gp-bg: #07101d;
      --gp-panel: rgba(12, 24, 46, 0.82);
      --gp-ink: #eaf7ff;
      --gp-muted: #9fb5cf;
      --gp-grid: rgba(234, 247, 255, 0.09);
      --gp-axis: rgba(234, 247, 255, 0.82);
      --gp-accent: #67e4ff;
      --gp-border: rgba(110, 210, 255, 0.22);
      --gp-border-strong: rgba(130, 228, 255, 0.4);
      color: var(--gp-ink);
    }
    .gameplay-stats-summary,
    .gameplay-stats-legend,
    .gameplay-stats-controls {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem 1rem;
      margin-bottom: 0.75rem;
      padding: 0.75rem 0.9rem;
      background: var(--gp-panel);
      border: 1px solid var(--gp-border);
      border-radius: 0.8rem;
      box-shadow: 0 0 0 1px rgba(103, 228, 255, 0.05), 0 18px 44px rgba(0, 0, 0, 0.26);
    }
    .gameplay-stats-summary .summary-card {
      min-width: 12rem;
      flex: 1 1 12rem;
    }
    .gameplay-stats-summary .summary-card strong,
    .gameplay-stats-summary .summary-card span {
      display: block;
    }
    .gameplay-stats-summary .summary-card span {
      color: var(--gp-muted);
      font-size: 0.85rem;
      margin-bottom: 0.15rem;
    }
    .gameplay-stats-summary .summary-card strong {
      font-size: 1rem;
    }
    .gameplay-stats-legend,
    .gameplay-stats-controls {
      font-size: 0.9rem;
      align-items: center;
    }
    .gameplay-stats-legend .legend-item,
    .gameplay-stats-controls label {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
    }
    .gameplay-stats-controls label { cursor: pointer; }
    .legend-shape {
      width: 0.8rem;
      height: 0.8rem;
      display: inline-block;
      background: var(--gp-accent);
    }
    .legend-line {
      width: 1.4rem;
      height: 0;
      border-top: 3px solid var(--gp-accent);
      display: inline-block;
    }
    .shape-circle { border-radius: 999px; }
    .shape-square { border-radius: 2px; }
    .shape-triangle { clip-path: polygon(50% 0%, 0% 100%, 100% 100%); }
    .shape-triangle-down { clip-path: polygon(0% 0%, 100% 0%, 50% 100%); }
    .shape-diamond { clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%); }
    .shape-hex { clip-path: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%); }
    .gameplay-stats-chart-shell {
      position: relative;
      background: var(--gp-panel);
      border: 1px solid var(--gp-border);
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 20px 48px rgba(0, 0, 0, 0.32), 0 0 0 1px rgba(103, 228, 255, 0.08);
    }
    .gameplay-stats-chart-shell svg {
      display: block;
      width: 100%;
      height: auto;
      background:
        radial-gradient(circle at top left, rgba(103,228,255,0.12), rgba(103,228,255,0) 28%),
        radial-gradient(circle at top right, rgba(112,183,255,0.1), rgba(112,183,255,0) 20%),
        linear-gradient(180deg, #0d1930 0%, #081120 100%);
    }
    .gameplay-stats-tooltip {
      position: absolute;
      min-width: 12rem;
      max-width: 18rem;
      padding: 0.6rem 0.75rem;
      border-radius: 0.75rem;
      background: rgba(24, 22, 18, 0.92);
      color: #fff;
      pointer-events: none;
      font-size: 0.85rem;
      line-height: 1.45;
      box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.2);
      visibility: hidden;
      white-space: pre-line;
      z-index: 10;
    }
    .gameplay-stats-footer {
      margin-top: 0.5rem;
      color: var(--gp-muted);
      font-size: 0.85rem;
    }
  </style>

  <script>
    (function () {
      const gameplayStats = <?php echo json_encode($this->gameplay_stats, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
      const svg = document.getElementById("gameplay-stats-chart");
      const tooltip = document.getElementById("gameplay-stats-tooltip");
      const shell = document.getElementById("gameplay-stats-shell");
      const summary = document.getElementById("gameplay-stats-summary");
      const seriesLegend = document.getElementById("gameplay-stats-series-legend");
      const legend = document.getElementById("gameplay-stats-legend");
      const controls = document.getElementById("gameplay-stats-controls");
      if (!svg || !gameplayStats || !gameplayStats.samples || !gameplayStats.samples.length) {
        return;
      }

      const ns = "http://www.w3.org/2000/svg";
      const width = 1280;
      const height = 760;
      const marginLeft = 72;
      const marginRight = 24;
      const marginTop = 36;
      const marginBottom = 56;
      const plotWidth = width - marginLeft - marginRight;
      const plotHeight = height - marginTop - marginBottom;
      const eventStyles = {
        buy_weapon: { label: "Buy weapon", shape: "circle" },
        buy_upgrade: { label: "Buy upgrade", shape: "square" },
        evolve: { label: "Evolve", shape: "triangle" },
        teambuy_bp: { label: "Team buy BP", shape: "diamond" },
        teambuy_unlock: { label: "Team unlock", shape: "triangle-down" },
        teambuy_upgrade: { label: "Team upgrade", shape: "hex" }
      };
      const series = [
        { key: "alien_net", label: "Aliens net", color: "#ff7c8f", visible: true },
        { key: "human_net", label: "Humans net", color: "#67a8ff", visible: true },
        { key: "alien_spent", label: "Aliens spent", color: "#ffb0bb", visible: true },
        { key: "human_spent", label: "Humans spent", color: "#8de8ff", visible: true }
      ];

      const samples = gameplayStats.samples;
      const events = gameplayStats.events || [];
      const xMin = samples[0].t;
      const xMax = samples[samples.length - 1].t > xMin ? samples[samples.length - 1].t : xMin + 1;
      let yMax = 0;
      for (const sample of samples) {
        yMax = Math.max(yMax, sample.alien_net, sample.human_net, sample.alien_spent, sample.human_spent);
      }
      for (const event of events) {
        if (typeof event.cumulative_spent === "number") {
          yMax = Math.max(yMax, event.cumulative_spent);
        }
      }
      yMax = Math.max(yMax, 1);

      const eventsByKind = {};
      for (const event of events) {
        eventsByKind[event.kind] = (eventsByKind[event.kind] || 0) + 1;
      }

      function escapeHtml(text) {
        return String(text)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;");
      }

      function appendSvg(tag, attrs, parent) {
        const node = document.createElementNS(ns, tag);
        for (const key in attrs) {
          node.setAttribute(key, attrs[key]);
        }
        parent.appendChild(node);
        return node;
      }

      function xScale(t) {
        return marginLeft + ((t - xMin) / (xMax - xMin)) * plotWidth;
      }

      function yScale(v) {
        return marginTop + plotHeight - (v / yMax) * plotHeight;
      }

      function shapePath(shape, x, y, size) {
        const h = size / 2;
        switch (shape) {
          case "triangle":
            return `M ${x} ${y - h} L ${x - h} ${y + h} L ${x + h} ${y + h} Z`;
          case "triangle-down":
            return `M ${x - h} ${y - h} L ${x + h} ${y - h} L ${x} ${y + h} Z`;
          case "diamond":
            return `M ${x} ${y - h} L ${x + h} ${y} L ${x} ${y + h} L ${x - h} ${y} Z`;
          case "hex":
            return `M ${x - h * 0.6} ${y - h} L ${x + h * 0.6} ${y - h} L ${x + h} ${y} L ${x + h * 0.6} ${y + h} L ${x - h * 0.6} ${y + h} L ${x - h} ${y} Z`;
          default:
            return `M ${x - h} ${y - h} L ${x + h} ${y - h} L ${x + h} ${y + h} L ${x - h} ${y + h} Z`;
        }
      }

      summary.innerHTML = [
        `<div class="summary-card"><span>Duration</span><strong>${xMax}s</strong></div>`,
        `<div class="summary-card"><span>Alien players</span><strong>${samples[samples.length - 1].aliens}</strong></div>`,
        `<div class="summary-card"><span>Human players</span><strong>${samples[samples.length - 1].humans}</strong></div>`,
        `<div class="summary-card"><span>Total events</span><strong>${events.length}</strong></div>`
      ].join("");

      const kinds = Object.keys(eventsByKind).sort();
      seriesLegend.innerHTML = `<strong>Series:</strong> ` + series.map((item) =>
        `<span class="legend-item"><span class="legend-line" style="--gp-accent: ${item.color}; border-top-color: ${item.color};"></span>${escapeHtml(item.label)}</span>`
      ).join("");
      legend.innerHTML = kinds.length
        ? `<strong>Event legend:</strong> ` + kinds.map((kind) => {
            const style = eventStyles[kind] || { label: kind, shape: "circle" };
            return `<span class="legend-item"><span class="legend-shape shape-${style.shape}"></span>${escapeHtml(style.label)}</span>`;
          }).join("")
        : `<span class="legend-item">No event kinds in this match</span>`;

      controls.innerHTML = series.map((item) =>
        `<label><input type="checkbox" data-series="${item.key}" checked> ${escapeHtml(item.label)}</label>`
      ).join("") + kinds.map((kind) => {
        const style = eventStyles[kind] || { label: kind };
        return `<label><input type="checkbox" data-kind="${escapeHtml(kind)}" checked> ${escapeHtml(style.label)}</label>`;
      }).join("");

      const root = appendSvg("g", {}, svg);
      appendSvg("rect", {
        x: marginLeft,
        y: marginTop,
        width: plotWidth,
        height: plotHeight,
        fill: "rgba(255,255,255,0.02)",
        stroke: "rgba(234,247,255,0.3)",
        "stroke-width": "2"
      }, root);
      for (let i = 0; i <= 5; i++) {
        const y = marginTop + (plotHeight * i / 5);
        appendSvg("line", { x1: marginLeft, y1: y, x2: marginLeft + plotWidth, y2: y, stroke: "rgba(234,247,255,0.09)", "stroke-width": "1" }, root);
        appendSvg("line", { x1: marginLeft - 8, y1: y, x2: marginLeft, y2: y, stroke: "rgba(234,247,255,0.82)", "stroke-width": "2" }, root);
        const labelValue = Math.round(yMax - (yMax * i / 5));
        const tick = appendSvg("text", { x: marginLeft - 12, y: y + 4, "text-anchor": "end", fill: "#eaf7ff", "font-size": "13", "font-weight": "600" }, root);
        tick.textContent = labelValue;
      }
      for (let i = 0; i <= 6; i++) {
        const t = xMin + ((xMax - xMin) * i / 6);
        const x = xScale(t);
        appendSvg("line", { x1: x, y1: marginTop, x2: x, y2: marginTop + plotHeight, stroke: "rgba(234,247,255,0.09)", "stroke-width": "1" }, root);
        appendSvg("line", { x1: x, y1: marginTop + plotHeight, x2: x, y2: marginTop + plotHeight + 8, stroke: "rgba(234,247,255,0.82)", "stroke-width": "2" }, root);
        const tick = appendSvg("text", { x: x, y: marginTop + plotHeight + 24, "text-anchor": "middle", fill: "#eaf7ff", "font-size": "13", "font-weight": "600" }, root);
        tick.textContent = `${Math.round(t)}s`;
      }
      appendSvg("line", { x1: marginLeft, y1: marginTop + plotHeight, x2: marginLeft + plotWidth, y2: marginTop + plotHeight, stroke: "rgba(234,247,255,0.88)", "stroke-width": "3" }, root);
      appendSvg("line", { x1: marginLeft, y1: marginTop, x2: marginLeft, y2: marginTop + plotHeight, stroke: "rgba(234,247,255,0.88)", "stroke-width": "3" }, root);
      const xAxisLabel = appendSvg("text", {
        x: marginLeft + (plotWidth / 2),
        y: height - 12,
        "text-anchor": "middle",
        fill: "#eaf7ff",
        "font-size": "16",
        "font-weight": "700"
      }, root);
      xAxisLabel.textContent = "Time (seconds)";
      const yAxisLabel = appendSvg("text", {
        x: 20,
        y: marginTop + (plotHeight / 2),
        transform: `rotate(-90 20 ${marginTop + (plotHeight / 2)})`,
        "text-anchor": "middle",
        fill: "#eaf7ff",
        "font-size": "16",
        "font-weight": "700"
      }, root);
      yAxisLabel.textContent = "Economy value";

      const seriesNodes = {};
      for (const item of series) {
        const points = samples.map((sample) => `${xScale(sample.t)},${yScale(sample[item.key])}`).join(" ");
        seriesNodes[item.key] = appendSvg("polyline", {
          points: points,
          fill: "none",
          stroke: item.color,
          "stroke-width": "3",
          "stroke-linejoin": "round",
          "stroke-linecap": "round"
        }, root);
      }

      const eventNodes = [];
      for (const event of events) {
        const style = eventStyles[event.kind] || { label: event.kind, shape: "circle" };
        const yValue = event.cumulative_spent;
        const x = xScale(event.t);
        const y = yScale(yValue);
        let node;
        if (style.shape === "circle") {
          node = appendSvg("circle", { cx: x, cy: y, r: 6, fill: event.team === "aliens" ? "#ff7c8f" : "#67a8ff", stroke: "#07101d", "stroke-width": "1.5" }, root);
        } else {
          node = appendSvg("path", { d: shapePath(style.shape, x, y, 12), fill: event.team === "aliens" ? "#ff7c8f" : "#67a8ff", stroke: "#07101d", "stroke-width": "1.5" }, root);
        }
        node.style.cursor = "pointer";
        node.dataset.kind = event.kind;
        node.dataset.tooltip = `t=${event.t}s\n${style.label}\n${event.team} client ${event.client}\n${event.item}\nCost: ${event.cost}`;
        eventNodes.push(node);
      }

      const hoverLine = appendSvg("line", {
        x1: marginLeft,
        y1: marginTop,
        x2: marginLeft,
        y2: marginTop + plotHeight,
        stroke: "#b24f2a",
        "stroke-width": "1.5",
        "stroke-dasharray": "6 4",
        visibility: "hidden"
      }, root);
      const hoverTarget = appendSvg("rect", {
        x: marginLeft,
        y: marginTop,
        width: plotWidth,
        height: plotHeight,
        fill: "transparent",
        "pointer-events": "none"
      }, root);

      function showTooltip(clientX, clientY, text) {
        const shellRect = shell.getBoundingClientRect();
        tooltip.textContent = text;
        let left = clientX - shellRect.left + 14;
        let top = clientY - shellRect.top + 14;
        const maxLeft = Math.max(8, shellRect.width - tooltip.offsetWidth - 8);
        const maxTop = Math.max(8, shellRect.height - tooltip.offsetHeight - 8);
        left = Math.min(Math.max(8, left), maxLeft);
        top = Math.min(Math.max(8, top), maxTop);
        tooltip.style.left = left + "px";
        tooltip.style.top = top + "px";
        tooltip.style.visibility = "visible";
      }

      function nearestSample(time) {
        let best = samples[0];
        let bestDistance = Math.abs(samples[0].t - time);
        for (const sample of samples) {
          const distance = Math.abs(sample.t - time);
          if (distance < bestDistance) {
            best = sample;
            bestDistance = distance;
          }
        }
        return best;
      }

      svg.addEventListener("mousemove", function (evt) {
        if (evt.target && evt.target.dataset && evt.target.dataset.tooltip) {
          return;
        }
        const rect = svg.getBoundingClientRect();
        const scaleX = width / rect.width;
        const scaleY = height / rect.height;
        const x = (evt.clientX - rect.left) * scaleX;
        const y = (evt.clientY - rect.top) * scaleY;
        if (x < marginLeft || x > marginLeft + plotWidth || y < marginTop || y > marginTop + plotHeight) {
          hoverLine.setAttribute("visibility", "hidden");
          tooltip.style.visibility = "hidden";
          return;
        }
        const clamped = Math.min(Math.max(x, marginLeft), marginLeft + plotWidth);
        const ratio = (clamped - marginLeft) / plotWidth;
        const time = xMin + ratio * (xMax - xMin);
        const sample = nearestSample(time);
        const lineX = xScale(sample.t);
        hoverLine.setAttribute("x1", lineX);
        hoverLine.setAttribute("x2", lineX);
        hoverLine.setAttribute("visibility", "visible");
        showTooltip(evt.clientX, evt.clientY,
          `t=${sample.t}s\nAliens net: ${sample.alien_net}\nHumans net: ${sample.human_net}\nAliens spent: ${sample.alien_spent}\nHumans spent: ${sample.human_spent}`
        );
      });

      svg.addEventListener("mouseleave", function () {
        hoverLine.setAttribute("visibility", "hidden");
        tooltip.style.visibility = "hidden";
      });

      for (const node of eventNodes) {
        node.addEventListener("mousemove", function (evt) {
          evt.stopPropagation();
          hoverLine.setAttribute("visibility", "hidden");
          showTooltip(evt.clientX, evt.clientY, node.dataset.tooltip);
        });
        node.addEventListener("mouseleave", function () {
          tooltip.style.visibility = "hidden";
        });
      }

      controls.addEventListener("change", function (evt) {
        const target = evt.target;
        if (target.dataset.series) {
          seriesNodes[target.dataset.series].style.display = target.checked ? "" : "none";
        } else if (target.dataset.kind) {
          for (const node of eventNodes) {
            if (node.dataset.kind === target.dataset.kind) {
              node.style.display = target.checked ? "" : "none";
            }
          }
        }
      });
    })();
  </script>
  <?php endif; ?>

</section>

 <?php include '__footer__.tpl.php'; ?>
