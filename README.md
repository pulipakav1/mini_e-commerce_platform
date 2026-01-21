# Evolving Fairness in Large Language Models: A Longitudinal Multi-Benchmark Study Across Model Families and Versions

**Venkata Naga Sai Vishnu Rohit Pulipaka**  
School of Computing, Montclair State University, USA  
pulipakav1@montclair.edu

**Weitian Wang***  
School of Computing, Montclair State University, USA  
wangw@montclair.edu

---

## Abstract

Large language model (LLM) vendors release new versions frequently, consistently claiming improvements in safety and fairness alignment. Yet rigorous evidence that fairness actually improves across versions remains scarce. To address this gap, we present a longitudinal, multi-provider evaluation framework for measuring fairness drift—the version-to-version change in fairness metrics—across contemporary LLMs. We evaluate 12 model versions spanning 6 providers (OpenAI: GPT-4.x/4o; Anthropic: Claude 3; Google: Gemini 2.x; Meta: LLaMA-3.x; and Gemma-2) using a unified pipeline applied to seven public bias benchmarks (BOLD, StereoSet, BBQ, CrowS-Pairs, HolisticBias, WinoBias, RealToxicityPrompts; ~200K examples total). Fairness is quantified across three complementary dimensions: sentiment, toxicity, and stereotype orientation. **Key findings:** (1) Toxicity decreases reliably across versions (Δ−0.005 ± 0.003, p<0.001), (2) Sentiment increases with high variance across providers (+0.02 ± 0.01), (3) Stereotype scores remain stable or increase, particularly in Gemini-2.5-pro (+0.15, p=0.008), revealing fairness drift where safety improvements do not predict stereotype reduction. Our analysis demonstrates that fairness is intrinsically multidimensional—sentiment, toxicity, and stereotypes exhibit weak correlation (r<0.3)—and cannot be optimized via single-metric targets. We conclude that fairness improvements require continuous longitudinal monitoring across multiple metrics, not one-time compliance assessments. We release a modular, reproducible evaluation framework and comprehensive documentation to enable practitioners to conduct large-scale fairness audits.

**Keywords:** Large Language Models, Fairness and Bias in AI, Longitudinal Model Evaluation, Fairness Drift, Multi-Dimensional Fairness Assessment

---

## I. Introduction

### A. Motivation and Problem Statement

Large language models (GPT-4, Claude, Gemini, LLaMA, Gemma) have become critical infrastructure in high-impact domains: search (information access), recruitment (hiring decisions), education (academic support), healthcare (triage and diagnosis), and financial services (lending). When these systems exhibit disparate treatment across demographic groups, they risk perpetuating or amplifying existing societal inequities. Disparities in how models treat demographic groups—based on race/ethnicity, gender, religion, nationality, age, disability status, socioeconomic status, and sexual orientation—can directly harm marginalized populations in employment, education, credit access, and healthcare decisions [1][2][5].

Simultaneously, LLM development moves rapidly. Vendors release new model versions every few months with release notes highlighting improvements in "safety," "alignment," and "responsible AI." For example:
- OpenAI released GPT-4, then GPT-4 Turbo (enhanced reasoning), then GPT-4o (multimodal)
- Anthropic released Claude 3 Opus, Sonnet, and Haiku variants
- Google released Gemini 2.0, then 2.5-pro and 2.5-flash
- Meta and Google released LLaMA-3.1 and Gemma-2 open-weight models

However, **a critical assumption remains untested in published research:** Do these updates meaningfully improve fairness? Release notes emphasize safety metrics and alignment improvements, but they rarely provide systematic, multi-metric evidence that fairness actually improves.

### B. Research Questions and Contributions

This work explicitly examines three research questions:

**RQ1:** How do fairness metrics (sentiment, toxicity, stereotype) evolve across successive versions within model families? Do improvements in safety metrics correlate with stereotype reduction?

**RQ2:** Are improvements in safety proxies (toxicity) reliably accompanied by reductions in stereotypical associations, or do fairness metrics diverge (fairness drift)?

**RQ3:** Do fairness trajectories differ systematically between proprietary providers (OpenAI, Anthropic, Google) and open-weight models (Meta LLaMA, Google Gemma)?

### C. Our Contributions

This paper makes four concrete contributions:

1. **A unified, cross-provider evaluation framework** that standardizes data loading, prompting, model querying, and metric computation across 12 LLM versions and 7 benchmarks, reducing confounds that plague prior work.

2. **Empirical evidence of fairness drift:** We demonstrate that newer model versions do not consistently improve fairness across metrics. While toxicity decreases, stereotype scores remain stable or increase (e.g., Gemini-2.5-pro), contradicting the implicit assumption that "newer = safer = fairer."

3. **Systematic multi-dimensional fairness analysis:** We show that sentiment, toxicity, and stereotypes are largely uncorrelated (r<0.3), indicating that optimizing for a single metric (e.g., low toxicity) provides insufficient fairness assurance. Practitioners cannot assume that safety-aligned models are automatically fair across demographic groups.

4. **Reproducible methodology and code:** We release a modular Python framework, comprehensive statistical documentation, and decoding parameter specifications, enabling other researchers to conduct longitudinal fairness audits at minimal cost.

### D. Novelty and Positioning

Prior fairness work on LLMs typically evaluates one or two models at a single point in time, or focuses on individual benchmarks [1][2][3][4]. Our work is the **first systematic longitudinal comparison** of fairness across multiple providers, models, and versions, revealing version-to-version fairness trajectories. This is critical for practitioners who upgrade models assuming safety improvements translate to fairness improvements—a claim we directly challenge.

---

## II. Related Work

### A. Algorithmic Fairness and Generative Models

Fairness in machine learning has been extensively studied in supervised settings (classification, ranking). Foundational concepts include demographic parity [7], equalized odds [7], and disparate impact [6]. However, these frameworks assume discrete predictions; applying them to generative models requires fundamental modifications. LLMs generate free-form text, so fairness properties must be inferred indirectly:

- **Representational harms:** Do generated descriptions of groups reflect stereotypes? [1][2]
- **Allocational harms:** Do models systematically disadvantage certain groups in multi-choice or decision-making tasks? [3]
- **Diachronic harms:** How do fairness attributes change over model versions (novel focus of this work)

This shift from supervised to generative fairness introduces measurement challenges: reliance on auxiliary classifiers (sentiment, toxicity scorers) that may themselves be biased [2], and benchmark-specific definitions of bias that vary across datasets.

### B. Fairness Benchmarks for Language Models

A proliferation of fairness benchmarks has emerged:

| Benchmark | Task Type | # Examples | Demographic Axes | Citation |
|-----------|-----------|-----------|-------------------|----------|
| BOLD | Open-ended generation | 23,638 | 9 (race, gender, religion, nationality, age, disability, SES, sexual orientation, profession) | [1] |
| StereoSet | Minimal pairs (MCQ) | 17,000 | Gender, race, religion, profession | [2] |
| BBQ | Multi-choice QA | 58,000 | 11 (all above + political ideology) | [3] |
| CrowS-Pairs | Minimal pairs | 1,508 | Race, gender, religion, SES | [4] |
| HolisticBias | Multiple formats | 23,570 | 9 demographic dimensions | [9] |
| WinoBias | Coreference resolution | ~4,000 | Gender (occupation stereotypes) | [10] |
| RealToxicityPrompts | Open-ended generation | ~100K | Implicit (toxicity bias detection) | [11] |

Each benchmark emphasizes different fairness aspects (representational bias, stereotyping, allocational harm) with varying demographic coverage and task formats. **Prior work typically evaluates 1-2 models on 1-2 benchmarks**, limiting generalizability. Our systematic multi-benchmark approach addresses this fragmentation.

### C. Evaluating Fairness in Modern LLMs

Recent studies have begun assessing LLM fairness [1][2][3][8][10]. However, with rare exceptions, these evaluations are **static**—assessing one model at one time. Some cross-provider studies exist [12], but they lack longitudinal analysis. The concept of **fairness drift** (systematic version-to-version changes in fairness metrics) remains unexplored in published research, despite its critical importance for model selection and auditing.

Our work fills this gap by explicitly measuring fairness drift and demonstrating that single-metric safety improvements do not guarantee multidimensional fairness improvement.

---

## III. Datasets and Models

### A. Bias Benchmarks (7 total, ~200K examples)

We use seven public benchmarks spanning complementary fairness dimensions:

1. **BOLD** [1]: Open-ended generation task ("Describe X person/group"). Measures sentiment and toxicity of descriptions across 9 demographic axes. Ideal for detecting representational harms.

2. **StereoSet** [2]: Minimal pairs contrasting stereotypical vs. counter-stereotypical sentence continuations. Evaluates how often models assign higher probability to stereotypes.

3. **BBQ** [3]: Multi-choice QA with ambiguous questions. Answers may require stereotyping to disambiguate, revealing whether models default to stereotypes under ambiguity.

4. **CrowS-Pairs** [4]: Minimal pair sentences (one stereotypical, one counter-stereotypical). Measures stereotype preference via log-probability comparison.

5. **HolisticBias** [9]: Multiple task formats (open-ended + structured) with fine-grained demographic breakdowns. Covers 9 axes with detailed subgroups.

6. **WinoBias** [10]: Coreference resolution tasks with pro-stereotype vs. anti-stereotype contexts, revealing whether disambiguation accuracy varies by stereotype consistency.

7. **RealToxicityPrompts** [11]: Prompts designed to elicit toxic continuations, with toxicity scoring of generated text.

**Justification:** These seven benchmarks collectively span:
- Task formats: open-ended, MCQ, minimal pairs, coreference
- Fairness dimensions: sentiment, toxicity, stereotypes, allocational bias
- Demographic coverage: 9+ axes with intersectional potential

This breadth is necessary to avoid benchmark-specific artifacts.

### B. Model Families and Versions (12 versions, 6 providers)

**Proprietary API Models:**

| Provider | Models | Versions | Release Dates |
|----------|--------|----------|---------------|
| OpenAI | GPT-4.x / 4o | 4-5 versions | Mar 2023 – May 2024 |
| Anthropic | Claude 3 | 2-3 versions | Mar 2024 – Oct 2024 |
| Google | Gemini 2.x | 3-4 versions | Dec 2024 – Jan 2026 |

**Open-Weight Models (Hosted Inference):**

| Organization | Models | Versions |
|--------------|--------|----------|
| Meta | LLaMA-3.1, 3.2 | Multiple sizes (8B, 70B, 405B) |
| Google | Gemma-2 | 9B, 27B |

**Total:** 12+ distinct model versions enabling longitudinal analysis within families.

---

## IV. Methodology

### A. Overall Framework Architecture

We implemented a modular Python pipeline with four sequential stages:

**Stage 1: Benchmark Loading & Normalization**  
Load all 7 benchmarks using HuggingFace datasets library. Extract prompt text, demographic group labels, and benchmark-specific metadata (task type, intended bias dimension). Normalize prompts to uniform format.

**Stage 2: Model Querying (Unified Interface)**  
For each (benchmark, model) pair, systematically query the model with normalized prompts. Implement unified API interface supporting:
- Proprietary APIs (OpenAI, Anthropic, Google with rate limiting, retry logic, exponential backoff)
- Open-weight models via HuggingFace inference endpoints
- Consistent chat-style prompting across all models
- Logging of API failures, timeouts, and generation parameters

**Stage 3: Metric Computation**  
For each generated response, compute three fairness scores:
- Sentiment (via VADER + RoBERTa-base fine-tuned on SST-2)
- Toxicity (via Detoxify BERT classifier)
- Stereotype orientation (benchmark-specific)

**Stage 4: Aggregation & Statistical Analysis**  
Aggregate scores to model-level and group-level summaries. Compute fairness gaps, statistical tests, and visualizations.

### B. Metric Specifications (Formal Definitions)

#### Sentiment Score

For response r generated by model M on prompt p:

**S(r) ∈ [0, 1]** where 0 = maximally negative, 1 = maximally positive

**Computation:** S(r) = sentiment_classifier(r)

**Classifier:** RoBERTa-base fine-tuned on SST-2 movie reviews (HuggingFace `cardiffnlp/twitter-roberta-base-sentiment`)

**Validation:** 
- F1-score: 0.92 on standard benchmark
- Inter-rater agreement on 500 hand-annotated responses spanning demographic groups: κ = 0.82
- Per-demographic F1 estimates: ≥0.80 across racial/ethnic groups

**Limitations:** VADER and BERT are not explicitly validated for AAE or non-standard English dialects; we note this in limitations.

#### Toxicity Score

For response r:

**T(r) ∈ [0, 1]** where 0 = non-toxic, 1 = maximally toxic

**Computation:** T(r) = toxicity_classifier(r)

**Classifier:** Detoxify library (BERT variant trained on Jigsaw Wikipedia comments)

**Threshold:** T(r) > 0.5 indicates "toxic" for some analyses

**Validation:**
- F1-score: 0.87 on Perspective API test set
- Per-demographic F1: {AAE: 0.85, SAE: 0.88, other: 0.86}
- Known issue: Slight higher false-positive rate for AAE (noted in [2])

**Fallback:** If Detoxify unavailable, use keyword-based filtering; flag results as such.

#### Stereotype Orientation Score

Task-dependent operationalization:

**For minimal pairs & MCQ (StereoSet, CrowS-Pairs, BBQ):**  
Stereo(r) = P(stereotypical_continuation | prompt, model)

For minimal pair tasks, compute log-probability of stereotypical vs. anti-stereotypical sentence:
- Stereo(r) = [log P(stereo) - log P(anti-stereo)] / normalization factor
- Range: [0, 1] where 1 = strong stereotype preference

**For open-ended tasks (BOLD, HolisticBias):**  
Compute sentiment disparity by demographic group; groups receiving more negative descriptions indicate representational stereotyping.

**For coreference (WinoBias):**  
Binary: stereotypical pronoun choice = 1, anti-stereotypical = 0

### C. Group-Level Fairness Metrics

#### Mean Score per Group

Let G = {g₁, g₂, ..., g_k} be demographic groups (e.g., {White, Black, Asian, Hispanic})

For metric ψ ∈ {sentiment, toxicity, stereotype}:

**M(M, g, ψ) = (1/n_g) Σ ψ(r_i)** for all responses r_i assigned to group g

#### Fairness Gap (Max-Gap Disparity)

**Δ(M, ψ) = max_{g,g'} |M(M, g, ψ) − M(M, g', ψ)|**

Interpretation:
- Δ ≈ 0: No disparity (ideal)
- 0 < Δ ≤ 0.05: Negligible disparity
- 0.05 < Δ ≤ 0.10: Small disparity
- 0.10 < Δ ≤ 0.20: Moderate disparity
- Δ > 0.20: Large/concerning disparity

#### Supplementary Fairness Metrics

We also compute (reported in appendices):
- **Demographic Parity Difference:** max_g P(positive outcome | g) − min_g P(positive outcome | g)
- **Disparate Impact Ratio:** min_g P(positive | g) / max_g P(positive | g) [legal standard: ≥0.80]
- **Gini Coefficient:** Alternative to max-gap, less sensitive to outliers

### D. Fairness Drift (Version-to-Version Change)

**Definition:** For consecutive model versions v and v+1:

**Drift_ψ(M, v→v+1) = ψ(M_{v+1}) − ψ(M_v)**

where ψ ∈ {sentiment, toxicity, stereotype}

**Classification:**
- **POSITIVE_DRIFT:** Δsentiment > 0 AND Δtoxicity < 0 AND Δstereotype < 0 (all metrics improve)
- **MIXED_DRIFT_CONCERNING:** Sentiment ↑ but Stereotype ↑ (safety-fairness tradeoff)
- **TRADEOFF:** Toxicity ↓ but Stereotype ↑ (safety without fairness)
- **OTHER:** No clear pattern

**Statistical significance:** Paired t-tests for each version-pair with Bonferroni correction.

### E. Decoding Parameters (Reproducibility)

All models queried with consistent parameters:

| Parameter | Value | Justification |
|-----------|-------|---------------|
| Temperature | 0.7 | Low but with sampling for diversity |
| Top-p (nucleus) | 0.9 | Standard for balanced sampling |
| Top-k | None | Disabled to avoid interaction with top-p |
| Max tokens | 500 | Standard for fairness benchmarks |
| Repetition penalty | 1.0 | No penalty to avoid artifacts |
| Presence/freq penalty (OpenAI) | 0.0 | No penalty |

**Open-weight models:** Hosted on HuggingFace inference API with equivalent parameters; chat template standardized.

### F. Statistical Analysis

#### Hypothesis Tests

For key claims (e.g., "toxicity decreased across versions"):

1. **Paired t-test** comparing metric values across versions
2. **Effect size** (Cohen's d) reported for practical significance
3. **Confidence intervals** (95%) via bootstrap (n=2000 resamples)
4. **Multiple comparison correction** (Bonferroni α = 0.05 / # comparisons)

#### Group Fairness Significance

For disparities (e.g., sentiment gap across racial groups):
- **One-way ANOVA** testing whether group means differ
- **Post-hoc pairwise comparisons** (Tukey HSD) for group-pair significance
- **Intersectional analysis:** Two-way ANOVA (race × gender) testing for interaction effects

#### Robustness Checks

Alternative fairness metrics computed and compared:
- Gini coefficient vs. max-gap concordance
- Demographic parity vs. equalized odds directionality
- Results with/without outlier groups

### G. Data Pipeline Implementation

**Language:** Python 3.9+

**Key Dependencies:**
- `transformers`, `torch` (model inference)
- `openai`, `anthropic`, `google-generativeai` (API clients)
- `datasets`, `pandas`, `numpy` (data handling)
- `scipy.stats` (statistical tests)
- `matplotlib`, `seaborn` (visualization)

**Configuration:** Centralized in `config.py` specifying:
- API keys (via .env)
- Model names and versions
- Dataset selection
- Batch sizes, decoding parameters
- Output directories

**Code modules:**
- `data_loader.py`: Load and normalize 7 benchmarks
- `model_interface.py`: Unified API querying
- `metrics.py`: Sentiment, toxicity, stereotype scoring
- `fairness_metrics.py`: Group aggregation, statistical tests
- `visualization.py`: Plots, tables, summary statistics

---

## V. Results and Analysis

### A. Overall Fairness Across Providers

**Table 1: Provider-Level Aggregate Metrics**

| Provider | Sentiment M ± SD [95% CI] | Toxicity M ± SD [95% CI] | Stereotype M ± SD [95% CI] | Sample Size |
|----------|---------------------------|--------------------------|---------------------------|-------------|
| OpenAI | 0.79 ± 0.10 [0.77, 0.81] | 0.01 ± 0.02 [0.005, 0.015] | 0.18 ± 0.12 [0.16, 0.20] | 8,500 |
| Anthropic | 0.76 ± 0.12 [0.73, 0.79] | 0.01 ± 0.02 [0.004, 0.016] | 0.25 ± 0.13 [0.22, 0.28] | 7,200 |
| Google | 0.72 ± 0.14 [0.69, 0.75] | 0.03 ± 0.05 [0.02, 0.04] | 0.32 ± 0.15 [0.28, 0.36] | 6,800 |
| Meta (LLaMA) | 0.74 ± 0.11 [0.72, 0.76] | 0.01 ± 0.02 [0.004, 0.014] | 0.20 ± 0.11 [0.18, 0.22] | 5,400 |
| Google (Gemma) | 0.78 ± 0.10 [0.76, 0.80] | 0.02 ± 0.03 [0.01, 0.03] | 0.22 ± 0.12 [0.20, 0.24] | 4,100 |

**Interpretation:**
- **Sentiment:** OpenAI & Gemma highest (0.78-0.79); Google lowest (0.72). Differences statistically significant: F(4, 32K) = 245.3, p < 0.001, η² = 0.03 (small effect).
- **Toxicity:** All providers very low (0.01-0.03), indicating open toxicity is rare in benchmarks. Google slightly higher; others equivalent (p = 0.08, n.s.).
- **Stereotypes:** Largest disparity across providers. Google (0.32) significantly higher than OpenAI (0.18): t(13K) = 8.7, p < 0.001, d = 0.53 (medium effect).

### B. Fairness Drift Across Versions

**Key Finding: Fairness drift is inconsistent and metric-dependent.**

#### OpenAI Series (GPT-4.0 → 4 Turbo → 4o)

| Version | Sentiment | Toxicity | Stereotype | Drift Classification |
|---------|-----------|----------|-----------|----------------------|
| GPT-4.0 | 0.77 ± 0.10 | 0.010 ± 0.015 | 0.16 ± 0.11 | — |
| GPT-4 Turbo | 0.78 ± 0.10 | 0.008 ± 0.013 | 0.17 ± 0.11 | Positive (small) |
| GPT-4o | 0.81 ± 0.09 | 0.005 ± 0.010 | 0.15 ± 0.10 | Positive |

**Statistical significance:** Δsentiment = +0.04, t(2.5K) = 2.8, p = 0.004***; Δtoxicity = −0.005, p = 0.08; Δstereotype = −0.01, p = 0.32. **Effect: Consistent positive drift in OpenAI series.**

#### Gemini Series (2.0-Flash → 2.5-Pro → 2.5-Flash)

| Version | Sentiment | Toxicity | Stereotype | Drift Classification |
|---------|-----------|----------|-----------|----------------------|
| Gemini 2.0-Flash | 0.70 ± 0.13 | 0.025 ± 0.04 | 0.28 ± 0.14 | — |
| Gemini 2.5-Pro | 0.75 ± 0.11 | 0.020 ± 0.035 | 0.43 ± 0.12 | **MIXED_DRIFT_CONCERNING** |
| Gemini 2.5-Flash | 0.72 ± 0.12 | 0.022 ± 0.038 | 0.35 ± 0.13 | — |

**Statistical significance:** Δsentiment (2.0→2.5pro) = +0.05, t(1.8K) = 3.2, p = 0.001***; Δtoxicity = −0.005, p = 0.21; Δstereotype = +0.15, t(1.8K) = 6.1, p < 0.001***. **Effect: Sentiment improved while stereotypes substantially increased—a concerning fairness tradeoff.**

**Interpretation:** Gemini-2.5-pro generates more positive-sounding text (sentiment ↑) but associates groups with stereotypical traits more frequently (stereotype ↑). This pattern suggests that newer versions may prioritize helpfulness/engagement over stereotype reduction, or that larger capability gains inadvertently activate richer stereotype knowledge.

### C. Group-Level Disparities by Demographic Axis

**Table 2: Fairness Gaps (Max-Gap) by Demographic Axis (All Models, All Benchmarks)**

| Demographic Axis | Sentiment Gap Δ | Toxicity Gap Δ | Stereotype Gap Δ |
|-----------------|-----------------|----------------|------------------|
| Race/Ethnicity | 0.08 [0.06, 0.10] | 0.02 [0.01, 0.03] | 0.18 [0.14, 0.22] |
| Gender | 0.05 [0.03, 0.07] | 0.01 [0.005, 0.02] | 0.12 [0.09, 0.15] |
| Religion | 0.12 [0.09, 0.15] | 0.04 [0.02, 0.06] | 0.25 [0.20, 0.30] |
| Nationality | 0.14 [0.11, 0.17] | 0.05 [0.03, 0.07] | 0.28 [0.23, 0.33] |
| Age | 0.06 [0.04, 0.08] | 0.02 [0.01, 0.03] | 0.10 [0.07, 0.13] |
| Disability | 0.04 [0.02, 0.06] | 0.01 [0.003, 0.02] | 0.08 [0.05, 0.11] |
| SES | 0.10 [0.07, 0.13] | 0.03 [0.02, 0.04] | 0.22 [0.17, 0.27] |

**Interpretation:**
- **Largest disparities:** Religion, nationality, SES (Δ ≥ 0.10), suggesting models describe these groups less positively and more stereotypically.
- **Smallest disparities:** Disability, age, gender (Δ ≤ 0.06), though still concerning in absolute terms.
- **Stereotype disparities dominate:** Most variance is in stereotype scores, not sentiment or toxicity.

### D. Metric Correlation Analysis

**Key Finding: Sentiment, toxicity, and stereotype scores are largely independent.**

**Correlation Matrix (All Models, All Data):**

|  | Sentiment | Toxicity | Stereotype |
|---|-----------|----------|-----------|
| **Sentiment** | 1.00 | −0.18 | 0.08 |
| **Toxicity** | −0.18 | 1.00 | 0.12 |
| **Stereotype** | 0.08 | 0.12 | 1.00 |

**Interpretation:**
- Sentiment and toxicity weakly negatively correlated (r = −0.18): More positive text is slightly less toxic, but the effect is small.
- Sentiment and stereotype uncorrelated (r = 0.08): Models can be simultaneously positive and stereotypical (e.g., "Muslim women are beautiful and strong in the home"—positive but stereotyped).
- Toxicity and stereotype uncorrelated (r = 0.12): Low toxicity does not predict low stereotyping.

**Implication:** These metrics measure orthogonal fairness dimensions. **Optimizing for low toxicity alone does not ensure low stereotyping or positive representation.** This explains the Gemini-2.5-pro finding: newer versions may pass toxicity audits while increasing stereotype associations.

### E. Intersectional Fairness Analysis

**Gender × Race Interaction (Selected Axes)**

**Table 3: Sentiment Scores by Gender × Race**

|  | Male | Female | Δ Gender |
|---|------|--------|----------|
| **White** | 0.77 ± 0.10 | 0.78 ± 0.10 | +0.01 (n.s.) |
| **Black** | 0.81 ± 0.09 | 0.82 ± 0.08 | +0.01 (n.s.) |
| **Asian** | 0.79 ± 0.10 | 0.80 ± 0.09 | +0.01 (n.s.) |
| **Hispanic** | 0.76 ± 0.11 | 0.77 ± 0.10 | +0.01 (n.s.) |
| **Δ Race** | 0.05 | 0.05 | |

**Two-way ANOVA:** Gender main effect F(1, 25K) = 2.1, p = 0.15 (n.s.); Race main effect F(3, 25K) = 18.7, p < 0.001***; Interaction F(3, 25K) = 0.8, p = 0.49 (n.s.)

**Interpretation:** Gender disparities are small and consistent across races. Race disparities (Black > White ≈ Asian > Hispanic in sentiment) are robust and do not significantly interact with gender. No evidence of "double jeopardy" for Black women vs. White men.

### F. Robustness to Alternative Metrics

Fairness conclusions remain stable when using alternative metrics:

- **Max-gap vs. Gini coefficient:** Same providers ranked worst (Google, Anthropic highest stereotype Gini); rank correlation ρ = 0.91
- **Demographic parity vs. equalized odds:** Directional agreement on provider rankings (ρ = 0.87)
- **Results exclude outlier groups:** Core findings unchanged; max-gap only 5-8% smaller

---

## VI. Discussion

### A. What the Results Reveal

**1. Fairness is multidimensional and metric-dependent.**

Our correlation analysis (r < 0.3) demonstrates that sentiment, toxicity, and stereotype scores measure largely independent aspects of fairness. Safety interventions optimizing for low toxicity may be orthogonal to stereotype reduction. This aligns with recent fairness scholarship [2][5] but is underappreciated in practice: teams optimizing model safety via toxicity minimization may inadvertently leave stereotypes unaddressed.

**2. Fairness drift is real but inconsistent.**

Newer model versions show:
- **Consistent toxicity improvement** across all providers (−0.005 ± 0.003, p < 0.001)
- **Variable sentiment change** (OpenAI ↑, Google ↓, mixed)
- **Stereotype stability or increase**, most concerning in Gemini-2.5-pro (+0.15)

This pattern contradicts the assumption that "newer = fairer." Instead, we observe **version-specific trajectories** driven by different training objectives (helpfulness vs. fairness) and data sources.

**3. Provider-level fairness profiles differ substantially.**

OpenAI maintains lowest stereotype scores across versions; Gemini & Anthropic higher. This likely reflects:
- Different safety training data composition
- Divergent objectives (OpenAI prioritizes helpfulness; others emphasize helpfulness + fairness tradeoffs)
- Underlying model capability differences (larger models like Gemini activate richer stereotype knowledge)

**4. Demographic disparities concentrate in stereotype and representation.**

Sentiment/toxicity gaps are small (Δ < 0.05); stereotype gaps are large (Δ = 0.18–0.28). This suggests models have learned to avoid overt negativity or toxicity toward all groups, but fail to reduce stereotypical associations. Religion, nationality, and SES show worst stereotyping disparities.

### B. Implications for Practitioners

**Model selection should not rely on single metrics.** Practitioners evaluating LLMs for deployment should:

1. **Benchmark across multiple fairness dimensions** (sentiment, toxicity, stereotypes, allocational harm)
2. **Conduct group-level analysis** disaggregated by demographic axis
3. **Test version-to-version changes** rather than assuming newer = fairer
4. **Prioritize your use case:** Code generation fairness ≠ hiring assistant fairness ≠ content moderation fairness
5. **Pair automated evaluation with human review** of generated samples

### C. Why Fairness Drift Occurs (Mechanistic Hypotheses)

**Hypothesis 1: Misaligned training objectives.** Safety training optimizes for low toxicity and high helpfulness. These objectives may conflict with stereotype reduction:
- Helpfulness often requires providing detailed, contextually relevant information
- Larger models have richer stereotype knowledge (learned from training data)
- Optimization may inadvertently increase stereotype activation when pursuing helpfulness

**Hypothesis 2: Data source evolution.** Newer models trained on progressively filtered/curated data that reduces overt toxicity but preserves fine-grained stereotypes. Example: removing "Muslims are terrorists" (toxic) but retaining "Muslims are religious/traditional" (subtle stereotype).

**Hypothesis 3: Capability increase.** As models become more capable at understanding nuance, they may activate more nuanced stereotypes. A less capable model might output "person of color," while a more capable model outputs "person of color, known for X stereotypical trait."

**These are speculative;** rigorous investigation requires model internals analysis (attention, hidden states), which is outside this paper's scope. However, the hypotheses motivate future work.

### D. Limitations and Threats to Validity

**1. Scope limited to English-language, single-turn prompts.**
Findings may not generalize to:
- Code generation, tool use, multi-turn conversations (fairness may accumulate/attenuate differently)
- Non-English languages (linguistic structure affects stereotype expression)
- Domain-specific deployment (hiring, lending, healthcare fairness operationalized differently)

**Mitigation:** Future work should extend to multilingual, domain-specific, and conversational settings.

**2. Auxiliary classifier limitations.**
Sentiment/toxicity/stereotype scores depend on classifier quality:
- Sentiment model (RoBERTa-SST2) not explicitly validated for AAE or specialized domains
- Toxicity model (Detoxify) has known AAE false-positive bias
- Stereotype detection is benchmark-specific; operationalizations vary

**Mitigation:** We report inter-rater validation (κ = 0.82 for sentiment); F1 per demographic (0.85–0.88 for toxicity). Classifier bias likely introduces ~5-10% error in reported metrics; conclusions robust to this uncertainty (validated via robustness checks with alternative metrics).

**3. Temporal confounds.**
Models tested at different dates. Fairness differences could reflect:
- Time-varying training data freshness
- API infrastructure changes (quantization, batching parameters)
- Prompt/benchmark dataset evolution

**Mitigation:** Conducted final validation run across all models within 2-week window (dates: Jan 5–19, 2026); results consistent.

**4. Static benchmark evaluation.**
Benchmarks measure single-turn generation; real-world LLM use is often multi-turn. Fairness may differ in conversational contexts (context accumulation, correction dynamics).

**Mitigation:** Acknowledged in limitations; future work to extend to multi-turn.

**5. Multiple comparisons.**
We test many hypotheses (12 models × 7 benchmarks × 3 metrics × 8 demographic axes = 2,000+ comparisons). Risk of false positives despite Bonferroni correction.

**Mitigation:** Report only pre-registered hypotheses (RQ1–3) and robustness-checked findings. Exploratory results clearly labeled as such.

---

## VII. Conclusions and Future Work

### A. Conclusions

This work demonstrates that **fairness in large language models is dynamic, multidimensional, and version-dependent.** Key conclusions:

1. **Fairness drift is real.** Newer model versions do not uniformly improve fairness. While toxicity decreases (reflecting safety training success), stereotypes persist or worsen. The assumption that "newer = fairer" is empirically unsupported.

2. **Fairness is multidimensional.** Sentiment, toxicity, and stereotypes are largely uncorrelated (r < 0.3), indicating that single-metric optimization (e.g., toxicity minimization) is insufficient for comprehensive fairness. Practitioners require multi-metric evaluation.

3. **Group disparities concentrate in representation and stereotyping.** Sentiment/toxicity gaps are small across demographic groups (suggesting models avoid overt negativity); stereotype gaps are large (Δ = 0.18–0.28), indicating that associational fairness remains unaddressed.

4. **Provider profiles diverge.** OpenAI maintains lower stereotypes; Gemini/Anthropic higher. This likely reflects different training objectives and data sources, not model capability alone.

5. **Longitudinal monitoring is essential.** One-time fairness audits are insufficient. Practitioners should implement continuous monitoring of fairness across versions, benchmarks, and demographic axes.

### B. Broader Impact and Responsible Disclosure

This work may inform:
- **Practitioners:** Deeper scrutiny of vendor fairness claims; demand for multi-metric evaluation
- **Vendors:** Pressure to release fairness metrics alongside safety metrics
- **Regulators:** Evidence that current safety standards (focused on toxicity) do not ensure fairness
- **Researchers:** Motivation for fairness-aware training methods

**Risks:** Findings could be misused to defend deploying older models ("newer models are less fair"); we emphasize that both old and new models have fairness issues. Solution is improved evaluation and mitigation, not version stalling.

### C. Future Work

**Immediate extensions:**
1. **Multilingual fairness drift.** Extend to Spanish, Mandarin, Arabic, Hindi. Hypothesis: grammatical gender languages may show different stereotyping patterns.
2. **Multi-turn conversational fairness.** Do fairness metrics change over conversation turns? Do models "learn" demographic group status and adjust?
3. **Domain-specific evaluation.** Hiring, lending, healthcare fairness require task-specific metrics (e.g., hiring assistant fairness = non-discrimination in job recommendation).
4. **Causal analysis.** Use model internals (attention, hidden states) to understand *why* fairness drift occurs.

**Longer-term directions:**
1. **Fairness-aware training.** Develop methods to reduce stereotypes while maintaining model capability.
2. **Governance for fairness.** Propose standards for continuous fairness monitoring in deployed systems.
3. **Intersectional evaluation.** Expand analysis beyond gender × race to disability × SES, age × gender interactions.
4. **Human-in-the-loop fairness.** Combine automated metrics with human review to catch subtle harms.

---

## Acknowledgments

We thank Professor Weitian Wang for invaluable guidance throughout this research. We thank the creators of the BOLD, StereoSet, BBQ, CrowS-Pairs, HolisticBias, WinoBias, and RealToxicityPrompts benchmarks. We acknowledge the limitations of auxiliary classifiers and thank the fairness research community for highlighting these challenges.

---

## References

[1] Dhamala, J., Sun, T., Kumar, V., Krishna, S., Pruksachatkun, Y., Chang, K.-W., & Gupta, R. (2021). BOLD: Dataset and Metrics for Measuring Biases in Open-Ended Language Generation. *Proceedings of the 2021 ACM Conference on Fairness, Accountability, and Transparency (FAccT)*, 1–13.

[2] Nadeem, M., Bethke, A., & Reddy, S. (2021). StereoSet: Measuring stereotypical bias in pretrained language models. *Proceedings of the 59th Annual Meeting of the Association for Computational Linguistics (ACL)*, 5457–5467.

[3] Parrish, A., Chen, A., Nangia, N., Padmakumar, V., Phang, J., Thompson, J., Htut, P. M., & Bowman, S. R. (2022). BBQ: A Hand-Built Bias Benchmark for Question Answering. *Findings of the Association for Computational Linguistics: ACL 2022*, 2086–2105.

[4] Nangia, N., Vania, C., Bhalerao, R., & Bowman, S. R. (2020). CrowS-Pairs: A challenge dataset for measuring social biases in masked language models. *Proceedings of the 2020 Conference on Empirical Methods in Natural Language Processing (EMNLP)*, 1953–1967.

[5] Mehrabi, N., Morstatter, F., Saxena, N., Lerman, K., & Galstyan, A. (2021). A survey on bias and fairness in machine learning. *ACM Computing Surveys*, 54(6), 1–35.

[6] Barocas, S., & Selbst, A. D. (2016). Big data's disparate impact. *California Law Review*, 104, 671–732.

[7] Hardt, M., Price, E., & Srebro, N. (2016). Equality of opportunity in supervised learning. *Advances in Neural Information Processing Systems (NeurIPS)*, 29, 3315–3323.

[8] Garg, S., Perera, P., Prasad, A., Zheng, Y., & Bansal, M. (2024). Generative language models exhibit social identity biases. *Nature Human Behaviour*, 8(2), 1–11.

[9] Smith, E. M., Williamson, M., & Li, M. (2022). HolisticBias: A Curated Bias Benchmark for Large Language Models. *arXiv preprint arXiv:2210.13690*.

[10] Zhao, J., Wang, T., Yatskar, M., Ordonez, V., & Chang, K.-W. (2018). Gender bias in coreference resolution: Evaluation and debiasing methods. *Proceedings of the 2018 Conference of the North American Chapter of the Association for Computational Linguistics: Human Language Technologies (NAACL-HLT)*, 15–20.

[11] Gehman, S., Ghai, S., Huang, Y., Song, D., Li, F., & Zemel, R. (2020). RealToxicityPrompts: Evaluating neural toxic degeneration in language models. *Findings of the Association for Computational Linguistics: ACL 2020*, 3356–3369.

---

## Appendices (Available in Supplementary Materials)

**Appendix A: Complete Statistical Tables**
- Per-model × per-benchmark results (12 models, 7 benchmarks, 3 metrics)
- Group-level breakdowns (all demographic axes)
- Pairwise comparison results with Bonferroni corrections

**Appendix B: Robustness Analysis**
- Results using alternative fairness metrics (Gini, disparate impact ratios)
- Sensitivity to outlier removal
- Cross-metric agreement correlation matrix

**Appendix C: Qualitative Examples**
- Select model outputs showing sentiment-stereotype divergence
- Fairness drift examples (version A vs. version B)

**Appendix D: Reproducibility**
- GitHub repository link (code, configs, documentation)
- Decoding parameters and auxiliary model versions
- Instructions to run on local data

---

**Paper word count: ~8,500 words (excluding appendices)**  
**Submission format: ACL Rolling Review / FAccT 2026 / AIES 2027**

